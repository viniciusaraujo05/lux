<?php

namespace App\Services;

use App\Models\BibleExplanation;

class ExplanationQueryService
{
    /**
     * Find explanation in database
     */
    public function find(string $testament, string $book, int $chapter, ?string $verses = null): ?BibleExplanation
    {
        $query = BibleExplanation::select(['id', 'explanation_text', 'source', 'access_count'])
            ->where([
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

        return $query->first();
    }

    /**
     * Create new explanation
     */
    public function create(
        string $testament,
        string $book,
        int $chapter,
        ?string $verses,
        string $explanationJson,
        string $source
    ): BibleExplanation {
        return BibleExplanation::create([
            'testament' => $testament,
            'book' => $book,
            'chapter' => $chapter,
            'verses' => $verses,
            'explanation_text' => $explanationJson,
            'source' => $source,
            'access_count' => 1,
        ]);
    }

    /**
     * Get popular explanations for cache warming
     */
    public function getPopularExplanations(int $limit = 50): array
    {
        return BibleExplanation::select(['testament', 'book', 'chapter', 'verses', 'access_count'])
            ->orderBy('access_count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get explanations with negative feedback for regeneration
     */
    public function getExplanationsNeedingRegeneration(int $limit = 20): array
    {
        return BibleExplanation::select(['id', 'testament', 'book', 'chapter', 'verses'])
            ->whereRaw('negative_feedback_count > positive_feedback_count')
            ->where('negative_feedback_count', '>=', 3)
            ->orderBy('negative_feedback_count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Normalize verses string
     */
    public function normalizeVerses(?string $verses): ?string
    {
        if (is_string($verses)) {
            $verses = trim($verses);
        }

        if ($verses === '' || $verses === null) {
            return null;
        }

        $versesArray = preg_split('/\s*,\s*/', (string) $verses, -1, PREG_SPLIT_NO_EMPTY);
        $versesArray = array_map('intval', $versesArray);
        sort($versesArray);
        $versesArray = array_unique($versesArray);

        return implode(',', $versesArray);
    }
}
