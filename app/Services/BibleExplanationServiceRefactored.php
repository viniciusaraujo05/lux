<?php

namespace App\Services;

use App\Models\BibleExplanation;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BibleExplanationServiceRefactored
{
    private ExplanationQueryService $queryService;

    private ExplanationCacheService $cacheService;

    private ExplanationGenerationService $generationService;

    private ExplanationValidationService $validationService;

    public function __construct(
        ExplanationQueryService $queryService,
        ExplanationCacheService $cacheService,
        ExplanationGenerationService $generationService,
        ExplanationValidationService $validationService
    ) {
        $this->queryService = $queryService;
        $this->cacheService = $cacheService;
        $this->generationService = $generationService;
        $this->validationService = $validationService;
    }

    /**
     * Get explanation with optimized caching and generation
     */
    public function getExplanation(string $testament, string $book, int $chapter, ?string $verses = null): array
    {
        // Normalize verses
        $verses = $this->queryService->normalizeVerses($verses);

        // Build cache key
        $cacheKey = $this->cacheService->buildKey($testament, $book, $chapter, $verses);
        $lockKey = $this->cacheService->buildLockKey($testament, $book, $chapter, $verses);

        // 1. Try cache first
        $cached = $this->cacheService->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // 2. Try database
        $explanation = $this->queryService->find($testament, $book, $chapter, $verses);
        if ($explanation) {
            $explanation->incrementAccessCount();

            $result = $this->formatExplanationResult($explanation, 'db');

            // Cache with intelligent TTL based on popularity
            $this->cacheService->put($cacheKey, $result, $explanation->access_count);

            return $result;
        }

        // 3. Generate new explanation with lock to prevent duplicate API calls
        return $this->generateWithLock($lockKey, $cacheKey, $testament, $book, $chapter, $verses);
    }

    /**
     * Generate explanation with distributed lock
     */
    private function generateWithLock(
        string $lockKey,
        string $cacheKey,
        string $testament,
        string $book,
        int $chapter,
        ?string $verses
    ): array {
        $result = null;

        try {
            Cache::lock($lockKey, 45)->block(15, function () use (
                &$result,
                $cacheKey,
                $testament,
                $book,
                $chapter,
                $verses
            ) {
                // Re-check cache inside lock
                $cached = $this->cacheService->get($cacheKey);
                if ($cached !== null) {
                    $result = $cached;

                    return;
                }

                // Re-check database inside lock
                $explanation = $this->queryService->find($testament, $book, $chapter, $verses);
                if ($explanation) {
                    $explanation->incrementAccessCount();
                    $result = $this->formatExplanationResult($explanation, 'db');
                    $this->cacheService->put($cacheKey, $result, $explanation->access_count);

                    return;
                }

                // Generate via AI
                $generationResult = $this->generationService->generate($testament, $book, $chapter, $verses);

                if (! $generationResult['success']) {
                    // Return fallback
                    $result = [
                        'id' => null,
                        'origin' => 'fallback',
                        'explanation' => $generationResult['fallback'],
                        'source' => 'fallback',
                    ];

                    return;
                }

                // Save to database
                $saved = $this->saveExplanation(
                    $testament,
                    $book,
                    $chapter,
                    $verses,
                    $generationResult['json'],
                    $cacheKey
                );

                if ($saved) {
                    $result = [
                        'id' => $saved->id,
                        'origin' => 'api',
                        'explanation' => $generationResult['decoded'],
                        'source' => $saved->source,
                    ];
                    $this->cacheService->put($cacheKey, $result, 1);
                } else {
                    // Fallback if save failed
                    $result = [
                        'id' => null,
                        'origin' => 'api_unsaved',
                        'explanation' => $generationResult['decoded'],
                        'source' => 'api',
                    ];
                }
            });
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            Log::warning('Lock timeout while generating explanation', [
                'testament' => $testament,
                'book' => $book,
                'chapter' => $chapter,
                'verses' => $verses,
            ]);

            // Return fallback on lock timeout
            return [
                'id' => null,
                'origin' => 'fallback',
                'explanation' => $this->generationService->generate($testament, $book, $chapter, $verses)['fallback'] ?? [],
                'source' => 'fallback',
            ];
        }

        return $result ?? [
            'id' => null,
            'origin' => 'fallback',
            'explanation' => [],
            'source' => 'fallback',
        ];
    }

    /**
     * Save explanation to database with duplicate handling
     */
    private function saveExplanation(
        string $testament,
        string $book,
        int $chapter,
        ?string $verses,
        string $explanationJson,
        string $cacheKey
    ): ?BibleExplanation {
        try {
            $source = config('ai.openai.model', 'gpt-5').'-json';

            return $this->queryService->create(
                $testament,
                $book,
                $chapter,
                $verses,
                $explanationJson,
                $source
            );
        } catch (QueryException $e) {
            $code = (string) $e->getCode();
            $sqlState = is_array($e->errorInfo ?? null) ? (string) ($e->errorInfo[0] ?? '') : '';

            // Handle unique constraint violation (race condition)
            if ($code === '23505' || $sqlState === '23505') {
                Log::info('Duplicate explanation avoided via unique constraint', [
                    'testament' => $testament,
                    'book' => $book,
                    'chapter' => $chapter,
                    'verses' => $verses,
                ]);

                // Fetch the existing one
                $existing = $this->queryService->find($testament, $book, $chapter, $verses);
                if ($existing) {
                    $existing->incrementAccessCount();

                    return $existing;
                }
            }

            Log::error('Failed to save explanation', [
                'testament' => $testament,
                'book' => $book,
                'chapter' => $chapter,
                'verses' => $verses,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Format explanation result
     */
    private function formatExplanationResult(BibleExplanation $explanation, string $origin): array
    {
        $decodedExplanation = json_decode($explanation->explanation_text, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $decodedExplanation = $explanation->explanation_text;
        }

        return [
            'id' => $explanation->id,
            'origin' => $origin,
            'explanation' => $decodedExplanation,
            'source' => $explanation->source,
        ];
    }

    /**
     * Warm cache for popular passages
     */
    public function warmCache(): void
    {
        $popular = $this->queryService->getPopularExplanations(50);
        $this->cacheService->warmPopularPassages($popular);
    }

    /**
     * Clear cache for specific explanation
     */
    public function clearCache(string $testament, string $book, int $chapter, ?string $verses = null): void
    {
        $this->cacheService->forget($testament, $book, $chapter, $verses);
    }
}
