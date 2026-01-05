<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MetricsService
{
    private const METRICS_PREFIX = 'metrics:';

    private const METRICS_TTL = 60 * 60 * 24; // 24 hours

    /**
     * Record API call metrics
     */
    public function recordApiCall(
        string $endpoint,
        int $durationMs,
        bool $success,
        int $tokensUsed = 0,
        string $cacheStatus = 'miss'
    ): void {
        $date = now()->format('Y-m-d');
        $hour = now()->format('H');

        // Increment counters
        $this->increment("api_calls:{$date}");
        $this->increment("api_calls:{$date}:{$hour}");
        $this->increment("api_calls_by_endpoint:{$endpoint}:{$date}");

        if ($success) {
            $this->increment("api_calls_success:{$date}");
        } else {
            $this->increment("api_calls_failed:{$date}");
        }

        // Record duration
        $this->recordValue("api_duration:{$endpoint}:{$date}", $durationMs);

        // Record tokens
        if ($tokensUsed > 0) {
            $this->recordValue("tokens_used:{$date}", $tokensUsed);
            $this->recordValue("tokens_used:{$endpoint}:{$date}", $tokensUsed);
        }

        // Cache metrics
        $this->increment("cache_{$cacheStatus}:{$date}");
    }

    /**
     * Record explanation generation metrics
     */
    public function recordExplanationGeneration(
        string $testament,
        string $book,
        int $chapter,
        ?string $verses,
        string $origin,
        int $durationMs,
        int $attempt = 1
    ): void {
        $date = now()->format('Y-m-d');
        $type = $verses === null ? 'chapter' : 'verse';

        $this->increment("explanations_generated:{$date}");
        $this->increment("explanations_by_type:{$type}:{$date}");
        $this->increment("explanations_by_origin:{$origin}:{$date}");

        if ($attempt > 1) {
            $this->increment("explanations_retried:{$date}");
        }

        $this->recordValue("explanation_duration:{$type}:{$date}", $durationMs);
    }

    /**
     * Get metrics summary for a date
     */
    public function getSummary(?string $date = null): array
    {
        $date = $date ?? now()->format('Y-m-d');

        return [
            'date' => $date,
            'api_calls' => [
                'total' => $this->get("api_calls:{$date}", 0),
                'success' => $this->get("api_calls_success:{$date}", 0),
                'failed' => $this->get("api_calls_failed:{$date}", 0),
            ],
            'cache' => [
                'hits' => $this->get("cache_hit:{$date}", 0),
                'misses' => $this->get("cache_miss:{$date}", 0),
                'hit_rate' => $this->calculateHitRate($date),
            ],
            'explanations' => [
                'total' => $this->get("explanations_generated:{$date}", 0),
                'from_db' => $this->get("explanations_by_origin:db:{$date}", 0),
                'from_api' => $this->get("explanations_by_origin:api:{$date}", 0),
                'from_cache' => $this->get("explanations_by_origin:cache:{$date}", 0),
                'chapters' => $this->get("explanations_by_type:chapter:{$date}", 0),
                'verses' => $this->get("explanations_by_type:verse:{$date}", 0),
            ],
            'tokens' => [
                'total' => $this->get("tokens_used:{$date}", 0),
            ],
            'performance' => [
                'avg_duration_ms' => $this->getAverage("explanation_duration:verse:{$date}"),
            ],
        ];
    }

    /**
     * Calculate cache hit rate
     */
    private function calculateHitRate(string $date): float
    {
        $hits = $this->get("cache_hit:{$date}", 0);
        $misses = $this->get("cache_miss:{$date}", 0);
        $total = $hits + $misses;

        return $total > 0 ? round(($hits / $total) * 100, 2) : 0.0;
    }

    /**
     * Increment a counter
     */
    private function increment(string $key): void
    {
        try {
            $fullKey = self::METRICS_PREFIX.$key;
            Cache::increment($fullKey);
            Cache::expire($fullKey, self::METRICS_TTL);
        } catch (\Exception $e) {
            Log::debug('Failed to increment metric', ['key' => $key]);
        }
    }

    /**
     * Record a value (for averages)
     */
    private function recordValue(string $key, int $value): void
    {
        try {
            $fullKey = self::METRICS_PREFIX.$key;
            $current = Cache::get($fullKey, []);

            if (! is_array($current)) {
                $current = [];
            }

            $current[] = $value;

            // Keep only last 1000 values
            if (count($current) > 1000) {
                $current = array_slice($current, -1000);
            }

            Cache::put($fullKey, $current, self::METRICS_TTL);
        } catch (\Exception $e) {
            Log::debug('Failed to record metric value', ['key' => $key]);
        }
    }

    /**
     * Get a metric value
     */
    private function get(string $key, $default = null)
    {
        try {
            return Cache::get(self::METRICS_PREFIX.$key, $default);
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Calculate average from recorded values
     */
    private function getAverage(string $key): float
    {
        $values = $this->get($key, []);

        if (! is_array($values) || empty($values)) {
            return 0.0;
        }

        return round(array_sum($values) / count($values), 2);
    }

    /**
     * Clear old metrics
     */
    public function clearOldMetrics(int $daysToKeep = 7): void
    {
        $cutoffDate = now()->subDays($daysToKeep)->format('Y-m-d');

        Log::info('Clearing metrics older than', ['date' => $cutoffDate]);

        // This would need a more sophisticated implementation
        // For now, just log the intent
    }
}
