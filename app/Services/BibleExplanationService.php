<?php

namespace App\Services;

use App\Models\BibleExplanation;
use Illuminate\Support\Facades\Http;
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
        // Normalize the verses string (sort and remove duplicates)
        if ($verses) {
            $versesArray = explode(',', $verses);
            $versesArray = array_map('intval', $versesArray);
            sort($versesArray);
            $versesArray = array_unique($versesArray);
            $verses = implode(',', $versesArray);
        }

        // Find in database
        $explanation = BibleExplanation::where([
            'testament' => $testament,
            'book' => $book,
            'chapter' => $chapter,
            'verses' => $verses,
        ])->first();

        // If found, increment the access counter and return
        if ($explanation) {
            $explanation->incrementAccessCount();

            // Try to decode the text. If it fails, it's likely old HTML.
            // In a real scenario, you might want a migration strategy for old data.
            $decodedExplanation = json_decode($explanation->explanation_text, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // It's not valid JSON, treat as legacy HTML content or handle as an error
                // For now, we'll pass it as a string and let the frontend decide.
                $decodedExplanation = $explanation->explanation_text;
            }

            return [
                'id' => $explanation->id,
                'origin' => 'cache',
                'explanation' => $decodedExplanation,
                'source' => $explanation->source,
            ];
        }

        // Otherwise, generate a new explanation
        $explanationJson = $this->generateExplanationViaAPI($testament, $book, $chapter, $verses);

        // Save to database
        $newExplanation = BibleExplanation::create([
            'testament' => $testament,
            'book' => $book,
            'chapter' => $chapter,
            'verses' => $verses,
            'explanation_text' => $explanationJson, // Storing JSON string
            'source' => 'gpt-4-json', // New source identifier
            'access_count' => 1,
        ]);

        return [
            'id' => $newExplanation->id,
            'origin' => 'api',
            'explanation' => json_decode($explanationJson, true), // Decode for frontend
            'source' => 'gpt-4-json',
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
        $apiKey = config('services.perplexity.api_key');
        $model = config('services.perplexity.model', 'sonar-medium-online');

        try {
            $prompt = $this->buildPrompt($testament, $book, $chapter, $verses);

            $systemMessage = 'Você é um especialista em teologia e estudos bíblicos. Sua tarefa é gerar uma explicação detalhada de uma passagem bíblica e retornar a resposta ESTRITAMENTE em formato JSON. NÃO inclua nenhuma mensagem introdutória ou texto fora do objeto JSON.';

            $responseContent = $this->makeApiCallWithRetry($apiKey, $model, $systemMessage, $prompt);

            // 1. Extrair o JSON do bloco de markdown, se houver
            if (preg_match('/```json\s*({.*?})\s*```/s', $responseContent, $matches)) {
                $jsonString = $matches[1];
            } else {
                $jsonString = $responseContent;
            }

            // 2. Validar o JSON
            $decodedJson = json_decode($jsonString, true);
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

    private function makeApiCallWithRetry($apiKey, $model, $systemMessage, $prompt, $isRetry = false)
    {
        $messages = [
            ['role' => 'system', 'content' => $systemMessage],
            ['role' => 'user', 'content' => $prompt],
        ];

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $isRetry ? 0.5 : 0.6,
            'max_tokens' => 4000,
            'presence_penalty' => $isRetry ? 0.2 : 0.1,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(120)->post('https://api.perplexity.ai/chat/completions', $payload);

        if (! $response->successful()) {
            if (str_contains($response->body(), 'cURL error 28')) {
                Log::error('Perplexity API Timeout', ['status' => $response->status(), 'body' => $response->body()]);
                throw new \Exception('A requisição para a API demorou demais para responder.');
            }
            Log::error('Perplexity API Error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \Exception('Erro ao chamar a API Perplexity: '.$response->body());
        }

        $content = $response->json('choices.0.message.content', '');

        // Lógica de nova tentativa: se estiver vazia ou não for um JSON válido na primeira tentativa
        if (! $isRetry) {
            json_decode($content);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('Resposta inicial não é um JSON válido, tentando novamente.');
                $retrySystemMessage = 'ATENÇÃO: Sua resposta anterior não foi um JSON válido. Responda ESTRITAMENTE com o objeto JSON solicitado, sem nenhum texto ou formatação adicional.';

                return $this->makeApiCallWithRetry($apiKey, $model, $retrySystemMessage, $prompt, true);
            }
        }

        return $content;
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
            : 'Forneça uma exegese detalhada e profunda, preenchendo todas as 11 seções do JSON. Seja especialmente detalhado na análise de contexto, exegese versículo por versículo e aplicações práticas.';

        return <<<EOD
            Você é um teólogo cristão experiente, especialista em exegese bíblica, com profundo conhecimento dos textos originais, contexto histórico e aplicações práticas.
            Sua tarefa é analisar a passagem bíblica solicitada e retornar uma resposta ESTRITAMENTE em formato JSON, sem nenhum texto ou comentário fora do objeto JSON.

            PASSAGEM PARA ANÁLISE: {$passageText}.

            INSTRUÇÕES GERAIS:
            - Escreva EXCLUSIVAMENTE em português brasileiro, num tom respeitoso, acolhedor e profundo.
            - Baseie-se em teólogos confiáveis (John Stott, R.C. Sproul, F.F. Bruce, Martyn Lloyd-Jones, Craig Keener, Hernandes Dias Lopes, Augustus Nicodemus) e fontes acadêmicas (NICOT/NICNT, Word Biblical Commentary).
            - Mantenha fidelidade às Escrituras e ao contexto original.
            - Não repita informações entre as seções do JSON.
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
        "pecados_e_virtudes": { "introducao": "string", "pecados": ["string"], "virtudes": ["string"] },
        "personagens_principais": { "introducao": "string", "personagens": [ { "nome": "string", "descricao": "string" } ] },
        "aplicacao_contemporanea": { "introducao": "string", "pontos_aplicacao": ["string"], "perguntas_reflexao": ["string"] },
        "referencias_cruzadas": { "introducao": "string", "referencias": [ { "passagem": "string", "explicacao": "string" } ] },
        "simbologia_biblica": { "introducao": "string", "simbolos": [ { "simbolo": "string", "significado": "string" } ] },
        "interprete_luz_de_cristo": { "introducao": "string", "conexao": "string" }
        }
        JSON;
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
}
