<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExplanationCacheService
{
    /**
     * Get TTL based on access count (popularity-based caching)
     */
    public function getTTL(int $accessCount = 0): int
    {
        if ($accessCount >= 100) {
            return 60 * 60 * 24 * 7; // 7 days for very popular
        } elseif ($accessCount >= 50) {
            return 60 * 60 * 24 * 3; // 3 days for popular
        } elseif ($accessCount >= 10) {
            return 60 * 60 * 24; // 1 day for moderate
        } else {
            return 60 * 60 * 4; // 4 hours for new content
        }
    }

    /**
     * Get from cache with fallback strategy
     */
    public function get(string $cacheKey): ?array
    {
        try {
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }

            // Try Redis as fallback if available
            if ($this->isRedisAvailable()) {
                $redisCached = Cache::store('redis')->get($cacheKey);
                if (is_array($redisCached)) {
                    // Restore to default cache
                    Cache::put($cacheKey, $redisCached, $this->getTTL());

                    return $redisCached;
                }
            }
        } catch (\Exception $e) {
            Log::debug('Cache retrieval failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Store in cache with intelligent TTL
     */
    public function put(string $cacheKey, array $data, int $accessCount = 0): void
    {
        $ttl = $this->getTTL($accessCount);

        try {
            Cache::put($cacheKey, $data, $ttl);

            // Also store in Redis if available for redundancy
            if ($this->isRedisAvailable()) {
                Cache::store('redis')->put($cacheKey, $data, $ttl);
            }
        } catch (\Exception $e) {
            Log::warning('Cache storage failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Build cache key for explanation
     */
    public function buildKey(string $testament, string $book, int $chapter, ?string $verses = null): string
    {
        return "bible_explanation:{$testament}:{$book}:{$chapter}:".($verses ?? 'all');
    }

    /**
     * Build lock key for explanation
     */
    public function buildLockKey(string $testament, string $book, int $chapter, ?string $verses = null): string
    {
        return "bible_explanation:lock:{$testament}:{$book}:{$chapter}:".($verses ?? 'all');
    }

    /**
     * Check if Redis is available
     */
    private function isRedisAvailable(): bool
    {
        static $available = null;

        if ($available === null) {
            try {
                $available = config('cache.stores.redis') !== null;
                if ($available) {
                    // Test connection
                    Cache::store('redis')->get('test');
                }
            } catch (\Exception $e) {
                $available = false;
            }
        }

        return $available;
    }

    /**
     * Warm cache for popular passages
     */
    public function warmPopularPassages(array $passages): void
    {
        foreach ($passages as $passage) {
            $cacheKey = $this->buildKey(
                $passage['testament'],
                $passage['book'],
                $passage['chapter'],
                $passage['verses'] ?? null
            );

            // Check if already cached
            if ($this->get($cacheKey) !== null) {
                continue;
            }

            // Queue for generation (would be implemented with job system)
            Log::info('Passage queued for cache warming', $passage);
        }
    }

    /**
     * Clear cache for specific explanation
     */
    public function forget(string $testament, string $book, int $chapter, ?string $verses = null): void
    {
        $cacheKey = $this->buildKey($testament, $book, $chapter, $verses);

        try {
            Cache::forget($cacheKey);
            if ($this->isRedisAvailable()) {
                Cache::store('redis')->forget($cacheKey);
            }
        } catch (\Exception $e) {
            Log::warning('Cache clear failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
