<?php

namespace App\Services;

use App\Services\Ai\AiClientFactory;
use App\Services\Ai\PromptBuilderService;
use Illuminate\Support\Facades\Log;

class ExplanationGenerationService
{
    private PromptBuilderService $promptBuilder;

    private ExplanationValidationService $validator;

    public function __construct(
        PromptBuilderService $promptBuilder,
        ExplanationValidationService $validator
    ) {
        $this->promptBuilder = $promptBuilder;
        $this->validator = $validator;
    }

    /**
     * Generate explanation via AI with retry strategy
     */
    public function generate(
        string $testament,
        string $book,
        int $chapter,
        ?string $verses = null
    ): array {
        $isFullChapter = $verses === null;
        $maxAttempts = 3;
        $lastError = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $result = $this->attemptGeneration($testament, $book, $chapter, $verses, $isFullChapter, $attempt);

                if ($result['success']) {
                    return [
                        'success' => true,
                        'json' => $result['json'],
                        'decoded' => $result['decoded'],
                        'attempt' => $attempt,
                    ];
                }

                $lastError = $result['error'];

                // Log attempt failure
                Log::warning('AI generation attempt failed', [
                    'attempt' => $attempt,
                    'max_attempts' => $maxAttempts,
                    'book' => $book,
                    'chapter' => $chapter,
                    'verses' => $verses,
                    'error' => $lastError,
                ]);

                // Wait before retry (exponential backoff)
                if ($attempt < $maxAttempts) {
                    usleep(500000 * $attempt); // 0.5s, 1s, 1.5s
                }

            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                Log::error('AI generation exception', [
                    'attempt' => $attempt,
                    'message' => $e->getMessage(),
                    'book' => $book,
                    'chapter' => $chapter,
                    'verses' => $verses,
                ]);

                if ($attempt < $maxAttempts) {
                    usleep(1000000 * $attempt); // 1s, 2s
                }
            }
        }

        // All attempts failed
        return [
            'success' => false,
            'error' => $lastError ?? 'Unknown error',
            'fallback' => $this->generateFallback($testament, $book, $chapter, $verses),
        ];
    }

    /**
     * Attempt to generate explanation
     */
    private function attemptGeneration(
        string $testament,
        string $book,
        int $chapter,
        ?string $verses,
        bool $isFullChapter,
        int $attempt
    ): array {
        $client = AiClientFactory::make();

        // Build prompt
        $prompt = $isFullChapter
            ? $this->promptBuilder->buildChapterSummaryPrompt($testament, $book, $chapter)
            : $this->promptBuilder->buildVerseExplanationPrompt($testament, $book, $chapter, $verses);

        $systemMessage = $this->promptBuilder->getSystemMessage();

        $messages = [
            ['role' => 'system', 'content' => $systemMessage],
            ['role' => 'user', 'content' => $prompt],
        ];

        // Adjust token budget based on attempt
        $baseTokens = $isFullChapter ? 3500 : 3000;
        $maxTokens = $baseTokens + ($attempt > 1 ? 1000 : 0); // Increase on retry

        // Call AI
        $responseContent = $client->chat($messages, $maxTokens);

        // Extract JSON from markdown if needed (also cleans control characters)
        $jsonString = $this->validator->extractJsonFromMarkdown($responseContent);

        // Try multiple strategies to parse JSON
        $decoded = null;
        $lastError = null;

        // Strategy 1: Direct decode
        $decoded = json_decode($jsonString, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            // Success!
        } else {
            $lastError = json_last_error_msg();

            // Strategy 2: Try to repair truncated JSON
            $repaired = $this->validator->repairJson($jsonString);
            if ($repaired !== null) {
                $decoded = json_decode($repaired, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $jsonString = $repaired;
                    $lastError = null;
                }
            }

            // Strategy 3: Try to fix common issues (trailing commas, etc)
            if ($lastError !== null) {
                $fixed = $this->fixCommonJsonIssues($jsonString);
                $decoded = json_decode($fixed, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $jsonString = $fixed;
                    $lastError = null;
                }
            }
        }

        if ($lastError !== null || ! is_array($decoded)) {
            return [
                'success' => false,
                'error' => 'Invalid JSON: '.($lastError ?? 'Not an array'),
            ];
        }

        // Check for error type
        if (($decoded['type'] ?? null) === 'error') {
            return [
                'success' => false,
                'error' => 'AI returned error type',
            ];
        }

        // Validate schema
        if (! $this->validator->validate($decoded, $isFullChapter)) {
            // Try to normalize
            $normalized = $this->validator->normalize($decoded, $isFullChapter);
            if ($this->validator->validate($normalized, $isFullChapter)) {
                $decoded = $normalized;
                $jsonString = json_encode($normalized, JSON_UNESCAPED_UNICODE);
            } else {
                return [
                    'success' => false,
                    'error' => 'Schema validation failed',
                ];
            }
        }

        return [
            'success' => true,
            'json' => $jsonString,
            'decoded' => $decoded,
        ];
    }

    /**
     * Fix common JSON syntax issues
     */
    private function fixCommonJsonIssues(string $json): string
    {
        // Remove trailing commas before closing braces/brackets
        $json = preg_replace('/,(\s*[}\]])/', '$1', $json);

        // Fix unescaped quotes in strings (basic attempt)
        // This is a simple fix and may not catch all cases

        // Remove comments (// or /* */)
        $json = preg_replace('~//[^\n]*~', '', $json);
        $json = preg_replace('~/\*.*?\*/~s', '', $json);

        // Fix single quotes to double quotes (if not already escaped)
        // Only in keys and string values - this is a simplified approach

        return $json;
    }

    /**
     * Generate fallback explanation
     */
    private function generateFallback(
        string $testament,
        string $book,
        int $chapter,
        ?string $verses
    ): array {
        $isFullChapter = $verses === null;
        $errorTitle = $isFullChapter
            ? "Resumo de {$book} {$chapter}"
            : "Explicação de {$book} {$chapter}:{$verses}";

        return [
            'type' => 'error',
            'requestDetails' => [
                'book' => $book,
                'chapter' => $chapter,
                'verses' => $verses,
            ],
            'errorDetails' => [
                'title' => 'Serviço Temporariamente Indisponível',
                'message' => "Não foi possível gerar a explicação para {$errorTitle}. O serviço de IA pode estar temporariamente indisponível. Por favor, tente novamente em alguns instantes.",
                'suggestion' => 'Se o problema persistir, entre em contato com o suporte.',
            ],
        ];
    }
}
