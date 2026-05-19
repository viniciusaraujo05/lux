<?php

namespace App\Services;

use App\Models\BibleExplanation;

class ExplanationQueryService
{
    /**
     * Find explanation in database
     */
    public function find(string $testament, string $book, int $chapter, ?string $verses = null, string $version = 'nvi'): ?BibleExplanation
    {
        $query = BibleExplanation::select(['id', 'explanation_text', 'source', 'access_count'])
            ->where([
                'version' => $version,
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
     * Find an explanation for display, preferring the requested version
     * but falling back to any existing version for the same passage.
     */
    public function findForDisplay(string $testament, string $book, int $chapter, ?string $verses = null, string $version = 'nvi'): ?BibleExplanation
    {
        $preferred = $this->find($testament, $book, $chapter, $verses, $version);
        if ($preferred) {
            return $preferred;
        }

        $query = BibleExplanation::select(['id', 'explanation_text', 'source', 'access_count', 'version'])
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

        return $query->orderByRaw("CASE WHEN version = ? THEN 0 ELSE 1 END", [$version])->first();
    }

    /**
     * Create new explanation
     */
    public function create(
        string $testament,
        string $book,
        int $chapter,
        ?string $verses,
        string $version,
        string $explanationJson,
        string $source
    ): BibleExplanation {
        return BibleExplanation::create([
            'version' => $version,
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
