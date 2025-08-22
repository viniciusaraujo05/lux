<?php

namespace App\Services;

use App\Models\BibleExplanation;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BibleExplanationService
{
    /**
     * Get an explanation from the database or generate a new one using the API.
     *
     * @param  string  $testament
     * @param  string  $book
     * @param  int  $chapter
     * @param  string|null  $verses
     * @return array
     */
    public function getExplanation($testament, $book, $chapter, $verses = null)
    {
        // Normalize verses: treat empty as null; otherwise sort & dedupe
        if (is_string($verses)) {
            $verses = trim($verses);
        }
        if ($verses === '' || $verses === null) {
            $verses = null;
        } elseif ($verses) {
            $versesArray = preg_split('/\s*,\s*/', (string) $verses, -1, PREG_SPLIT_NO_EMPTY);
            $versesArray = array_map('intval', $versesArray);
            sort($versesArray);
            $versesArray = array_unique($versesArray);
            $verses = implode(',', $versesArray);
        }

        // $testament is already provided by method argument; no reassignment needed
        $cacheKey = "bible_explanation:{$testament}:{$book}:{$chapter}:".($verses ?? 'all');
        $ttl = 60 * 60 * 24; // 24h
        $lockKey = "bible_explanation:lock:{$testament}:{$book}:{$chapter}:".($verses ?? 'all');

        // 1) Cache first
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        // 2) Database lookup (tolerant to NULL/empty verses)
        $query = BibleExplanation::where([
            'testament' => $testament,
            'book' => $book,
            'chapter' => $chapter,
        ]);
        if ($verses === null) {
            $query->where(function ($q) {
                $q->whereNull('verses')->orWhere('verses', '');
            });
        } else {
            $query->where('verses', $verses);
        }
        $explanation = $query->first();

        if ($explanation) {
            $explanation->incrementAccessCount();

            $decodedExplanation = json_decode($explanation->explanation_text, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $decodedExplanation = $explanation->explanation_text;
            }

            $result = [
                'id' => $explanation->id,
                'origin' => 'db',
                'explanation' => $decodedExplanation,
                'source' => $explanation->source,
            ];
            Cache::put($cacheKey, $result, $ttl);

            return $result;
        }

        // 3) Single-flight lock to avoid duplicate API calls for the same key
        $resultFromLock = null;
        Cache::lock($lockKey, 60)->block(20, function () use (&$resultFromLock, $cacheKey, $ttl, $testament, $book, $chapter, $verses) {
            // Re-check cache inside the lock
            $cachedInside = Cache::get($cacheKey);
            if (is_array($cachedInside)) {
                $resultFromLock = $cachedInside;
                return;
            }

            // Re-check DB inside the lock
            $query = BibleExplanation::where([
                'testament' => $testament,
                'book' => $book,
                'chapter' => $chapter,
            ]);
            if ($verses === null) {
                $query->where(function ($q) {
                    $q->whereNull('verses')->orWhere('verses', '');
                });
            } else {
                $query->where('verses', $verses);
            }
            $explanation = $query->first();
            if ($explanation) {
                $explanation->incrementAccessCount();

                $decodedExplanation = json_decode($explanation->explanation_text, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $decodedExplanation = $explanation->explanation_text;
                }

                $result = [
                    'id' => $explanation->id,
                    'origin' => 'db',
                    'explanation' => $decodedExplanation,
                    'source' => $explanation->source,
                ];
                Cache::put($cacheKey, $result, $ttl);
                $resultFromLock = $result;
                return;
            }

            // 3) Generate via AI
            $explanationJson = app(self::class)->generateExplanationViaAPI($testament, $book, $chapter, $verses);

            // 3.1) Strictly validate JSON before persisting
            $decoded = json_decode($explanationJson, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                // Never persist malformed JSON
                Log::error('AI returned invalid JSON, not persisting', [
                    'json_error' => json_last_error_msg(),
                    'book' => $book,
                    'chapter' => $chapter,
                    'verses' => $verses,
                ]);
                $fallback = json_decode(app(self::class)->generateFallbackExplanation($testament, $book, $chapter, $verses), true);

                $resultFromLock = [
                    'id' => null,
                    'origin' => 'fallback',
                    'explanation' => $fallback,
                    'source' => 'fallback',
                ];
                return;
            }

            if (($decoded['type'] ?? null) === 'error') {
                // Fallback content: do NOT persist or cache
                $resultFromLock = [
                    'id' => null,
                    'origin' => 'fallback',
                    'explanation' => $decoded,
                    'source' => 'fallback',
                ];
                return;
            }

            // 3.2) Validate minimal schema to avoid persisting wrong-shaped content
            $isFullChapter = ($verses === null);
            if (! app(self::class)->validateExplanationSchema($decoded, $isFullChapter)) {
                // Attempt to normalize/coerce into the expected schema
                $normalized = app(self::class)->normalizeExplanationSchema($decoded, $isFullChapter);
                if (app(self::class)->validateExplanationSchema($normalized, $isFullChapter)) {
                    // Use normalized JSON going forward
                    $explanationJson = json_encode($normalized, JSON_UNESCAPED_UNICODE);
                    $decoded = $normalized;
                } else {
                    Log::error('AI returned JSON with invalid schema, not persisting', [
                        'book' => $book,
                        'chapter' => $chapter,
                        'verses' => $verses,
                    ]);
                    $fallback = json_decode(app(self::class)->generateFallbackExplanation($testament, $book, $chapter, $verses), true);

                    $resultFromLock = [
                        'id' => null,
                        'origin' => 'fallback',
                        'explanation' => $fallback,
                        'source' => 'fallback',
                    ];
                    return;
                }
            }

            // 3.3) Double-check DB before insert (in case another process saved it while we generated)
            $existingQuery = BibleExplanation::where([
                'testament' => $testament,
                'book' => $book,
                'chapter' => $chapter,
            ]);
            if ($verses === null) {
                $existingQuery->where(function ($q) {
                    $q->whereNull('verses')->orWhere('verses', '');
                });
            } else {
                $existingQuery->where('verses', $verses);
            }
            $existing = $existingQuery->first();
            if ($existing) {
                $existing->incrementAccessCount();
                $decodedExplanation = json_decode($existing->explanation_text, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $decodedExplanation = $existing->explanation_text;
                }
                $result = [
                    'id' => $existing->id,
                    'origin' => 'db',
                    'explanation' => $decodedExplanation,
                    'source' => $existing->source,
                ];
                Cache::put($cacheKey, $result, $ttl);
                $resultFromLock = $result;
                return;
            }

            // 4) Persist successful explanation and cache it (handle unique constraint race)
            try {
                $newExplanation = BibleExplanation::create([
                    'testament' => $testament,
                    'book' => $book,
                    'chapter' => $chapter,
                    'verses' => $verses,
                    'explanation_text' => $explanationJson, // Storing JSON string
                    'source' => config('ai.openai.model', 'openai').'-json', // Source identifier from configured model
                    'access_count' => 1,
                ]);

                $result = [
                    'id' => $newExplanation->id,
                    'origin' => 'api',
                    'explanation' => json_decode($explanationJson, true), // Decode for frontend
                    'source' => config('ai.openai.model', 'openai').'-json',
                ];
                Cache::put($cacheKey, $result, $ttl);

                $resultFromLock = $result;
                return;
            } catch (QueryException $e) {
                $code = (string) $e->getCode();
                $sqlState = is_array($e->errorInfo ?? null) ? (string) ($e->errorInfo[0] ?? '') : '';
                if ($code === '23505' || $sqlState === '23505') { // Postgres unique violation
                    Log::info('Duplicate explanation insert avoided via unique constraint', [
                        'testament' => $testament,
                        'book' => $book,
                        'chapter' => $chapter,
                        'verses' => $verses,
                    ]);
                    $existing = BibleExplanation::where([
                        'testament' => $testament,
                        'book' => $book,
                        'chapter' => $chapter,
                        'verses' => $verses,
                    ])->first();
                    if ($existing) {
                        $existing->incrementAccessCount();
                        $decodedExplanation = json_decode($existing->explanation_text, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $decodedExplanation = $existing->explanation_text;
                        }
                        $result = [
                            'id' => $existing->id,
                            'origin' => 'db',
                            'explanation' => $decodedExplanation,
                            'source' => $existing->source,
                        ];
                        Cache::put($cacheKey, $result, $ttl);

                        $resultFromLock = $result;
                        return;
                    }
                }
                throw $e;
            }
        });

        if (is_array($resultFromLock)) {
            return $resultFromLock;
        }

        // As a last resort, return a fallback error structure
        return [
            'id' => null,
            'origin' => 'fallback',
            'explanation' => json_decode($this->generateFallbackExplanation($testament, $book, $chapter, $verses), true),
            'source' => 'fallback',
        ];
    }

    /**
     * Generate an explanation using an AI API (example implementation).
     *
     * @param  string  $testament
     * @param  string  $book
     * @param  int  $chapter
     * @param  string|null  $verses
     * @return string
     */
    private function generateExplanationViaAPI($testament, $book, $chapter, $verses = null)
    {
        $client = \App\Services\Ai\AiClientFactory::make();

        try {
            $prompt = $this->buildPrompt($testament, $book, $chapter, $verses);

            $systemMessage = 'Você é um especialista em teologia e estudos bíblicos. Responda ESTRITAMENTE com um único objeto JSON válido. NÃO use markdown, NÃO use cercas de código (```), NÃO inclua texto antes ou depois do JSON. O retorno deve ser apenas o objeto JSON.';

            $messages = [
                ['role' => 'system', 'content' => $systemMessage],
                ['role' => 'user', 'content' => $prompt],
            ];

            // Dynamic output budgets to reduce truncation
            $maxTokens = $verses === null ? 3000 : 2200;
            $responseContent = $client->chat($messages, $maxTokens);

            // 1. Extrair o JSON do bloco de markdown, se houver
            if (preg_match('/```json\s*({.*?})\s*```/s', $responseContent, $matches)) {
                $jsonString = $matches[1];
            } else {
                $jsonString = $responseContent;
            }

            // 2. Validar o JSON (com tentativa de reparo em caso de truncamento)
            $decodedJson = json_decode($jsonString, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Tentar reparar extraindo o maior prefixo JSON balanceado
                $repaired = $this->tryRepairJson($jsonString);
                if ($repaired !== null) {
                    $jsonString = $repaired;
                    $decodedJson = json_decode($jsonString, true);
                }
            }
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON received from API', ['json_error' => json_last_error_msg(), 'content' => $jsonString]);
                throw new \Exception('A API retornou um JSON inválido.');
            }

            return $jsonString; // Retorna a string JSON validada

        } catch (\Exception $e) {
            Log::error('Exception when generating explanation', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->generateFallbackExplanation($testament, $book, $chapter, $verses);
        }
    }

    /**
     * Build the prompt for the AI API.
     *
     * @param  string  $testament
     * @param  string  $book
     * @param  int  $chapter
     * @param  string|null  $verses
     * @return string
     */
    private function buildPrompt($testament, $book, $chapter, $verses = null)
    {
        $isFullChapter = $verses === null;
        $passageText = $isFullChapter
            ? "o capítulo {$chapter} do livro de {$book} ({$testament} Testamento)"
            : "os versículos {$verses} do capítulo {$chapter} do livro de {$book} ({$testament} Testamento)";

        $jsonStructure = $isFullChapter
            ? $this->getChapterSummaryJsonStructure()
            : $this->getVerseExplanationJsonStructure();

        $specificInstructions = $isFullChapter
            ? 'Sua tarefa é fornecer uma análise completa do capítulo. Preencha todos os campos do JSON. Em `contexto_geral.contexto_do_livro`, forneça informações detalhadas sobre autoria, data, audiência, propósito e o cenário histórico-cultural. Em `contexto_geral.contexto_do_capitulo_no_livro`, explique como o capítulo se conecta com o restante do livro. Para as outras chaves, forneça um resumo, temas, personagens, versículos-chave e uma aplicação prática relevante.'
            : 'Forneça uma exegese detalhada e profunda, preenchendo todas as 11 seções do JSON. Seja especialmente detalhado na análise de contexto, exegese versículo por versículo e na explicação do versículo. Na seção "explicacao_do_versiculo", ofereça uma análise profunda do significado do versículo, seu contexto original, palavras-chave importantes e sua interpretação teológica.';

        return <<<EOD
            Você é um teólogo cristão experiente, especialista em exegese bíblica, com profundo conhecimento dos textos originais, contexto histórico e aplicações práticas.
            Sua tarefa é analisar a passagem bíblica solicitada e retornar uma resposta ESTRITAMENTE em formato JSON, sem nenhum texto ou comentário fora do objeto JSON.

            PASSAGEM BÍBLICA EM PORTUGUES BRASILEIRO PARA ANÁLISE: {$passageText}.

            INSTRUÇÕES GERAIS:
            - Escreva EXCLUSIVAMENTE em português brasileiro, num tom respeitoso, acolhedor e profundo.
            - Use uma linguagem acessível e compreensível para todos os leitores.
            - Baseie-se em teólogos confiáveis (John Stott, R.C. Sproul, F.F. Bruce, Martyn Lloyd-Jones, Craig Keener, Hernandes Dias Lopes, Augustus Nicodemus) e fontes acadêmicas (NICOT/NICNT, Word Biblical Commentary).
            - Mantenha fidelidade às Escrituras e ao contexto original.
            - Não repita informações entre as seções do JSON.
            - NÃO use markdown e NÃO use cercas de código (```); retorne apenas um ÚNICO objeto JSON válido.
            - Nunca omita chaves da estrutura: se algum campo não se aplicar ou se não tiver certeza, MANTENHA a chave e use string vazia "" ou array vazio [] conforme o tipo esperado.
            - Seja conciso: limite cada campo textual a 2-3 frases no máximo; listas/arrays devem ter no máximo 3 itens; em "analises", inclua no máximo 3 objetos.
            - {$specificInstructions}

            ESTRUTURA JSON DE RETORNO OBRIGATÓRIA:
            Siga EXATAMENTE esta estrutura JSON. Não adicione, remova ou renomeie nenhuma chave.

            {$jsonStructure}
EOD;
    }

    /**
     * Generate a fallback explanation when the API is not available.
     *
     * @param  string  $testament
     * @param  string  $book
     * @param  int  $chapter
     * @param  string|null  $verses
     * @return string
     */
    private function generateFallbackExplanation($testament, $book, $chapter, $verses = null)
    {
        $isFullChapter = $verses === null;
        $errorTitle = $isFullChapter
            ? "Resumo de {$book} {$chapter}"
            : "Explicação de {$book} {$chapter}:{$verses}";

        $fallbackData = [
            'type' => 'error',
            'requestDetails' => [
                'book' => $book,
                'chapter' => $chapter,
                'verses' => $verses,
            ],
            'errorDetails' => [
                'title' => 'Serviço Indisponível no Momento',
                'message' => 'Não foi possível gerar a explicação para '.$errorTitle.'. A API de inteligência artificial pode estar temporariamente indisponível ou sobrecarregada. Por favor, tente novamente em alguns instantes.',
                'suggestion' => 'Se o problema persistir, entre em contato com o suporte.',
            ],
        ];

        return json_encode($fallbackData);
    }

    private function getVerseExplanationJsonStructure()
    {
        return <<<'JSON'
        {
        "titulo_principal_e_texto_biblico": { "titulo": "string", "texto": "string" },
        "contexto_detalhado": { "introducao": "string", "contexto_literario_do_livro": "string", "contexto_historico_do_livro": "string", "contexto_cultural_do_livro": "string", "contexto_geografico_do_livro": "string", "contexto_teologico_do_livro": "string", "contexto_literario_da_passagem": "string", "contexto_historico_da_passagem": "string", "contexto_cultural_da_passagem": "string", "contexto_geografico_da_passagem": "string", "contexto_teologico_da_passagem_anterior": "string", "contexto_teologico_da_passagem_posterior": "string", "genero_literario": "string", "autor_e_data": "string", "audiencia_original": "string", "proposito_principal": "string", "estrutura_e_esboco": "string", "palavras_chave_e_temas": "string" },
        "analise_exegenetica": { "introducao": "string", "analises": [ { "verso": "string", "analise": "string" } ] },
        "teologia_da_passagem": { "introducao": "string", "doutrinas": ["string"] },
        "temas_principais": { "introducao": "string", "temas": [ { "tema": "string", "descricao": "string" } ] },
        "explicacao_do_versiculo": { "introducao": "string", "significado_profundo": "string", "contexto_original": "string", "palavras_chave": ["string"], "interpretacao_teologica": "string" },
        "personagens_principais": { "introducao": "string", "personagens": [ { "nome": "string", "descricao": "string" } ] },
        "aplicacao_contemporanea": { "introducao": "string", "pontos_aplicacao": ["string"], "perguntas_reflexao": ["string"] },
        "referencias_cruzadas": { "introducao": "string", "referencias": [ { "passagem": "string", "explicacao": "string" } ] },
        "simbologia_biblica": { "introducao": "string", "simbolos": [ { "simbolo": "string", "significado": "string" } ] },
        "interprete_luz_de_cristo": { "introducao": "string", "conexao": "string" }
        }
        JSON;
    }

    /**
     * Tenta reparar um JSON possivelmente truncado, retornando o maior prefixo
     * com chaves balanceadas. Ignora chaves dentro de strings com escaping básico.
     * Retorna null se não for possível reparar.
     */
    private function tryRepairJson(string $text): ?string
    {
        $start = strpos($text, '{');
        if ($start === false) {
            return null;
        }
        $inString = false;
        $escape = false;
        $depth = 0;
        $lastBalanced = null;
        $len = strlen($text);
        for ($i = $start; $i < $len; $i++) {
            $ch = $text[$i];
            if ($inString) {
                if ($escape) {
                    $escape = false;
                } elseif ($ch === '\\') {
                    $escape = true;
                } elseif ($ch === '"') {
                    $inString = false;
                }

                continue;
            }
            if ($ch === '"') {
                $inString = true;

                continue;
            }
            if ($ch === '{') {
                $depth++;
            } elseif ($ch === '}') {
                $depth--;
                if ($depth === 0) {
                    $lastBalanced = $i;
                    // Poderia haver mais objetos depois; continue para pegar o maior
                }
            }
        }
        if ($lastBalanced !== null) {
            $candidate = substr($text, $start, $lastBalanced - $start + 1);
            $decoded = json_decode($candidate, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $candidate;
            }
        }

        return null;
    }

    private function getChapterSummaryJsonStructure()
    {
        return <<<'JSON'
        {
          "contexto_geral": {
            "contexto_do_livro": {
              "autor_e_data": "string (Quem escreveu o livro e aproximadamente quando)",
              "audiencia_original": "string (Para quem o livro foi originalmente escrito)",
              "proposito_do_livro": "string (Qual o objetivo principal do livro)",
              "contexto_historico_cultural": "string (Qual era a situação histórica e cultural da época)"
            },
            "contexto_do_capitulo_no_livro": "string (Como este capítulo se encaixa na estrutura e nos temas gerais do livro)"
          },
          "resumo_do_capitulo": "string (Um resumo claro e objetivo dos principais eventos e ensinamentos do capítulo)",
          "temas_principais": ["string (Liste os temas centrais abordados no capítulo)"],
          "personagens_importantes": ["string (Liste os personagens principais que aparecem ou são mencionados e sua importância)"],
          "versiculos_chave": ["string (Cite 2-3 versículos que são fundamentais para o entendimento do capítulo)"],
          "aplicacao_pratica": "string (Que lições práticas e relevantes podemos tirar para os dias de hoje)"
        }
        JSON;
    }

    /**
     * Minimal schema validation to ensure expected keys and basic types exist.
     * Prevents persisting malformed or wrong-shaped JSON.
     */
    private function validateExplanationSchema(array $data, bool $isFullChapter): bool
    {
        if ($isFullChapter) {
            $required = [
                'contexto_geral',
                'resumo_do_capitulo',
                'temas_principais',
                'personagens_importantes',
                'versiculos_chave',
                'aplicacao_pratica',
            ];
            foreach ($required as $k) {
                if (! array_key_exists($k, $data)) {
                    return false;
                }
            }
            if (! is_array($data['temas_principais'])) {
                return false;
            }
            if (! is_array($data['personagens_importantes'])) {
                return false;
            }
            if (! is_array($data['versiculos_chave'])) {
                return false;
            }

            return true;
        }

        // Verse-level explanation required keys
        $required = [
            'titulo_principal_e_texto_biblico',
            'contexto_detalhado',
            'analise_exegenetica',
            'teologia_da_passagem',
            'temas_principais',
            'explicacao_do_versiculo',
            'personagens_principais',
            'aplicacao_contemporanea',
            'referencias_cruzadas',
            'simbologia_biblica',
            'interprete_luz_de_cristo',
        ];
        foreach ($required as $k) {
            if (! array_key_exists($k, $data)) {
                return false;
            }
        }
        // Check some nested array types when present
        if (isset($data['analise_exegenetica']['analises']) && ! is_array($data['analise_exegenetica']['analises'])) {
            return false;
        }
        if (isset($data['temas_principais']['temas']) && ! is_array($data['temas_principais']['temas'])) {
            return false;
        }
        if (isset($data['explicacao_do_versiculo']['palavras_chave']) && ! is_array($data['explicacao_do_versiculo']['palavras_chave'])) {
            return false;
        }
        if (isset($data['personagens_principais']['personagens']) && ! is_array($data['personagens_principais']['personagens'])) {
            return false;
        }
        if (isset($data['aplicacao_contemporanea']['pontos_aplicacao']) && ! is_array($data['aplicacao_contemporanea']['pontos_aplicacao'])) {
            return false;
        }
        if (isset($data['aplicacao_contemporanea']['perguntas_reflexao']) && ! is_array($data['aplicacao_contemporanea']['perguntas_reflexao'])) {
            return false;
        }
        if (isset($data['referencias_cruzadas']['referencias']) && ! is_array($data['referencias_cruzadas']['referencias'])) {
            return false;
        }

        return true;
    }

    /**
     * Normalize/coerce AI response into the expected schema by filling missing keys
     * and fixing obvious type mismatches (strings vs arrays). This aims to prevent
     * schema validation rejections for minor omissions.
     */
    private function normalizeExplanationSchema(array $data, bool $isFullChapter): array
    {
        if ($isFullChapter) {
            $out = $data;
            // Ensure top-level keys
            if (!isset($out['contexto_geral']) || !is_array($out['contexto_geral'])) {
                $out['contexto_geral'] = [];
            }
            if (!isset($out['contexto_geral']['contexto_do_livro']) || !is_array($out['contexto_geral']['contexto_do_livro'])) {
                $out['contexto_geral']['contexto_do_livro'] = [];
            }
            $cdl = & $out['contexto_geral']['contexto_do_livro'];
            $cdl['autor_e_data'] = isset($cdl['autor_e_data']) ? (string) $cdl['autor_e_data'] : '';
            $cdl['audiencia_original'] = isset($cdl['audiencia_original']) ? (string) $cdl['audiencia_original'] : '';
            $cdl['proposito_do_livro'] = isset($cdl['proposito_do_livro']) ? (string) $cdl['proposito_do_livro'] : '';
            $cdl['contexto_historico_cultural'] = isset($cdl['contexto_historico_cultural']) ? (string) $cdl['contexto_historico_cultural'] : '';

            $out['contexto_geral']['contexto_do_capitulo_no_livro'] = isset($out['contexto_geral']['contexto_do_capitulo_no_livro']) ? (string) $out['contexto_geral']['contexto_do_capitulo_no_livro'] : '';

            $out['resumo_do_capitulo'] = isset($out['resumo_do_capitulo']) ? (string) $out['resumo_do_capitulo'] : '';
            $out['temas_principais'] = isset($out['temas_principais']) && is_array($out['temas_principais']) ? $out['temas_principais'] : [];
            $out['personagens_importantes'] = isset($out['personagens_importantes']) && is_array($out['personagens_importantes']) ? $out['personagens_importantes'] : [];
            $out['versiculos_chave'] = isset($out['versiculos_chave']) && is_array($out['versiculos_chave']) ? $out['versiculos_chave'] : [];
            $out['aplicacao_pratica'] = isset($out['aplicacao_pratica']) ? (string) $out['aplicacao_pratica'] : '';

            return $out;
        }

        // Verse-level
        $defaults = [
            'titulo_principal_e_texto_biblico' => ['titulo' => '', 'texto' => ''],
            'contexto_detalhado' => [
                'introducao' => '',
                'contexto_literario_do_livro' => '',
                'contexto_historico_do_livro' => '',
                'contexto_cultural_do_livro' => '',
                'contexto_geografico_do_livro' => '',
                'contexto_teologico_do_livro' => '',
                'contexto_literario_da_passagem' => '',
                'contexto_historico_da_passagem' => '',
                'contexto_cultural_da_passagem' => '',
                'contexto_geografico_da_passagem' => '',
                'contexto_teologico_da_passagem_anterior' => '',
                'contexto_teologico_da_passagem_posterior' => '',
                'genero_literario' => '',
                'autor_e_data' => '',
                'audiencia_original' => '',
                'proposito_principal' => '',
                'estrutura_e_esboco' => '',
                'palavras_chave_e_temas' => '',
            ],
            'analise_exegenetica' => ['introducao' => '', 'analises' => []],
            'teologia_da_passagem' => ['introducao' => '', 'doutrinas' => []],
            'temas_principais' => ['introducao' => '', 'temas' => []],
            'explicacao_do_versiculo' => [
                'introducao' => '',
                'significado_profundo' => '',
                'contexto_original' => '',
                'palavras_chave' => [],
                'interpretacao_teologica' => '',
            ],
            'personagens_principais' => ['introducao' => '', 'personagens' => []],
            'aplicacao_contemporanea' => ['introducao' => '', 'pontos_aplicacao' => [], 'perguntas_reflexao' => []],
            'referencias_cruzadas' => ['introducao' => '', 'referencias' => []],
            'simbologia_biblica' => ['introducao' => '', 'simbolos' => []],
            'interprete_luz_de_cristo' => ['introducao' => '', 'conexao' => ''],
        ];

        $out = $data;
        foreach ($defaults as $key => $defVal) {
            if (!isset($out[$key]) || !is_array($out[$key])) {
                $out[$key] = $defVal;
                continue;
            }
            // Merge shallowly; coerce types for known array fields
            foreach ($defVal as $subKey => $subDef) {
                if (!isset($out[$key][$subKey])) {
                    $out[$key][$subKey] = $subDef;
                } else {
                    if (is_array($subDef)) {
                        // Ensure array type
                        if (!is_array($out[$key][$subKey])) {
                            $out[$key][$subKey] = [];
                        }
                    } else {
                        // Ensure string type
                        if (is_array($out[$key][$subKey])) {
                            $out[$key][$subKey] = '';
                        } else {
                            $out[$key][$subKey] = (string) $out[$key][$subKey];
                        }
                    }
                }
            }
        }

        // Extra coercions for arrays the validator checks explicitly
        if (isset($out['analise_exegenetica']['analises']) && !is_array($out['analise_exegenetica']['analises'])) {
            $out['analise_exegenetica']['analises'] = [];
        }
        if (isset($out['temas_principais']['temas']) && !is_array($out['temas_principais']['temas'])) {
            $out['temas_principais']['temas'] = [];
        }
        if (isset($out['explicacao_do_versiculo']['palavras_chave']) && !is_array($out['explicacao_do_versiculo']['palavras_chave'])) {
            $out['explicacao_do_versiculo']['palavras_chave'] = [];
        }
        if (isset($out['personagens_principais']['personagens']) && !is_array($out['personagens_principais']['personagens'])) {
            $out['personagens_principais']['personagens'] = [];
        }
        if (isset($out['aplicacao_contemporanea']['pontos_aplicacao']) && !is_array($out['aplicacao_contemporanea']['pontos_aplicacao'])) {
            $out['aplicacao_contemporanea']['pontos_aplicacao'] = [];
        }
        if (isset($out['aplicacao_contemporanea']['perguntas_reflexao']) && !is_array($out['aplicacao_contemporanea']['perguntas_reflexao'])) {
            $out['aplicacao_contemporanea']['perguntas_reflexao'] = [];
        }
        if (isset($out['referencias_cruzadas']['referencias']) && !is_array($out['referencias_cruzadas']['referencias'])) {
            $out['referencias_cruzadas']['referencias'] = [];
        }

        return $out;
    }
}
