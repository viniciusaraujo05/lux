<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ExplanationValidationService
{
    /**
     * Validate explanation schema
     */
    public function validate(array $data, bool $isFullChapter): bool
    {
        if ($isFullChapter) {
            return $this->validateChapterSchema($data);
        }

        return $this->validateVerseSchema($data);
    }

    /**
     * Validate chapter summary schema
     */
    private function validateChapterSchema(array $data): bool
    {
        $required = [
            'contexto_geral',
            'resumo_do_capitulo',
            'temas_principais',
        ];

        foreach ($required as $key) {
            if (! array_key_exists($key, $data)) {
                Log::debug('Missing required key in chapter schema', ['key' => $key]);

                return false;
            }
        }

        // Validate array types
        if (! is_array($data['temas_principais'] ?? null)) {
            return false;
        }

        return true;
    }

    /**
     * Validate verse explanation schema
     */
    private function validateVerseSchema(array $data): bool
    {
        $required = [
            'titulo_principal_e_texto_biblico',
            'contexto_detalhado',
            'analise_exegetica',
            'explicacao_do_versiculo',
            'aplicacao_contemporanea',
        ];

        foreach ($required as $key) {
            if (! array_key_exists($key, $data)) {
                Log::debug('Missing required key in verse schema', ['key' => $key]);

                return false;
            }
        }

        return true;
    }

    /**
     * Normalize explanation schema
     */
    public function normalize(array $data, bool $isFullChapter): array
    {
        if ($isFullChapter) {
            return $this->normalizeChapterSchema($data);
        }

        return $this->normalizeVerseSchema($data);
    }

    /**
     * Normalize chapter schema
     */
    private function normalizeChapterSchema(array $data): array
    {
        $defaults = [
            'contexto_geral' => [
                'contexto_do_livro' => [
                    'autor_e_data' => '',
                    'audiencia_original' => '',
                    'proposito_do_livro' => '',
                    'contexto_historico_cultural' => '',
                ],
                'contexto_do_capitulo_no_livro' => '',
            ],
            'resumo_do_capitulo' => '',
            'estrutura_do_capitulo' => ['divisoes' => []],
            'temas_principais' => [],
            'personagens_importantes' => [],
            'versiculos_chave' => [],
            'teologia_do_capitulo' => [
                'doutrinas' => [],
                'revelacao_de_deus' => '',
                'conexao_crista' => '',
            ],
            'aplicacao_pratica' => [
                'licoes' => [],
                'desafios' => [],
            ],
        ];

        return $this->mergeWithDefaults($data, $defaults);
    }

    /**
     * Normalize verse schema
     */
    private function normalizeVerseSchema(array $data): array
    {
        $defaults = [
            'titulo_principal_e_texto_biblico' => ['titulo' => '', 'texto' => ''],
            'contexto_detalhado' => [
                'introducao' => '',
                'contexto_literario' => '',
                'contexto_historico' => '',
                'contexto_teologico' => '',
            ],
            'analise_exegetica' => [
                'introducao' => '',
                'analises' => [],
            ],
            'explicacao_do_versiculo' => [
                'significado_profundo' => '',
                'contexto_original' => '',
                'palavras_chave' => [],
                'interpretacao_teologica' => '',
            ],
            'temas_principais' => ['temas' => []],
            'aplicacao_contemporanea' => [
                'pontos_aplicacao' => [],
                'perguntas_reflexao' => [],
            ],
            'referencias_cruzadas' => ['referencias' => []],
            'interprete_luz_de_cristo' => ['conexao' => ''],
        ];

        return $this->mergeWithDefaults($data, $defaults);
    }

    /**
     * Merge data with defaults recursively
     */
    private function mergeWithDefaults(array $data, array $defaults): array
    {
        $result = $data;

        foreach ($defaults as $key => $defaultValue) {
            if (! isset($result[$key])) {
                $result[$key] = $defaultValue;
            } elseif (is_array($defaultValue) && is_array($result[$key])) {
                $result[$key] = $this->mergeWithDefaults($result[$key], $defaultValue);
            }
        }

        return $result;
    }

    /**
     * Try to repair truncated JSON
     */
    public function repairJson(string $jsonString): ?string
    {
        $start = strpos($jsonString, '{');
        if ($start === false) {
            return null;
        }

        $inString = false;
        $escape = false;
        $depth = 0;
        $lastBalanced = null;
        $len = strlen($jsonString);

        for ($i = $start; $i < $len; $i++) {
            $ch = $jsonString[$i];

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
                }
            }
        }

        if ($lastBalanced !== null) {
            $candidate = substr($jsonString, $start, $lastBalanced - $start + 1);
            $decoded = json_decode($candidate, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Extract JSON from markdown code blocks and clean control characters
     */
    public function extractJsonFromMarkdown(string $content): string
    {
        if (preg_match('/```json\s*({.*?})\s*```/s', $content, $matches)) {
            return $this->cleanControlCharacters($matches[1]);
        }

        if (preg_match('/```\s*({.*?})\s*```/s', $content, $matches)) {
            return $this->cleanControlCharacters($matches[1]);
        }

        return $this->cleanControlCharacters($content);
    }

    /**
     * Clean control characters that break JSON parsing
     */
    private function cleanControlCharacters(string $json): string
    {
        // Remove control characters (0x00-0x1F) except tab, newline, carriage return
        $json = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $json);

        // Fix common issues
        $json = str_replace(["\r\n", "\r"], "\n", $json); // Normalize line endings
        $json = preg_replace('/\n\s*\n/', "\n", $json); // Remove extra blank lines

        return trim($json);
    }
}
