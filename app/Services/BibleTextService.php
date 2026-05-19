<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;

class BibleTextService
{
    private const SUPPORTED_VERSIONS = ['acf', 'nvi', 'aa'];

    public function getSupportedVersions(): array
    {
        return self::SUPPORTED_VERSIONS;
    }

    public function getChapter(string $version, string $bookSlug, int $chapter): array
    {
        $version = strtolower($version);
        $bookSlug = strtolower($bookSlug);

        if (! in_array($version, self::SUPPORTED_VERSIONS, true)) {
            throw new InvalidArgumentException('Versão da Bíblia não suportada.');
        }

        if ($chapter < 1) {
            throw new InvalidArgumentException('Capítulo inválido.');
        }

        $bookName = SlugService::slugParaLivro($bookSlug);
        $bibleData = $this->loadVersionData($version);
        $bookData = $this->findBookData($bibleData, $bookName);

        if (! $bookData) {
            throw new InvalidArgumentException('Livro não encontrado para esta versão.');
        }

        $chapterIndex = $chapter - 1;
        $chapterData = $bookData['chapters'][$chapterIndex] ?? null;
        if (! is_array($chapterData)) {
            throw new InvalidArgumentException('Capítulo não encontrado para este livro.');
        }

        $verses = [];
        foreach ($chapterData as $index => $verseText) {
            $verses[] = [
                'number' => $index + 1,
                'text' => $verseText,
            ];
        }

        return [
            'version' => $version,
            'book' => $bookName,
            'book_slug' => $bookSlug,
            'chapter' => $chapter,
            'verses' => $verses,
        ];
    }

    private function loadVersionData(string $version): array
    {
        $cacheKey = "bible_text:version:{$version}";

        return Cache::remember($cacheKey, 60 * 60 * 24, function () use ($version) {
            $path = public_path("data/bible-versions/{$version}.json");
            if (! File::exists($path)) {
                throw new InvalidArgumentException('Arquivo da versão bíblica não encontrado.');
            }

            $content = File::get($path);
            $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
            $decoded = json_decode($content, true);
            if (! is_array($decoded)) {
                throw new InvalidArgumentException('Arquivo da versão bíblica está inválido.');
            }

            return $decoded;
        });
    }

    private function findBookData(array $bibleData, string $bookName): ?array
    {
        $normalizedBookName = $this->normalizeBookName($bookName);
        foreach ($bibleData as $bookData) {
            $name = $bookData['name'] ?? null;
            if (! is_string($name)) {
                continue;
            }

            if ($this->normalizeBookName($name) === $normalizedBookName) {
                return $bookData;
            }
        }

        return null;
    }

    private function normalizeBookName(string $bookName): string
    {
        $normalized = mb_strtolower(trim($bookName));
        $normalized = str_replace('ções', 'coes', $normalized);
        $normalized = str_replace('ó', 'o', $normalized);
        $normalized = str_replace('â', 'a', $normalized);
        $normalized = str_replace('ê', 'e', $normalized);
        $normalized = str_replace('á', 'a', $normalized);
        $normalized = str_replace('é', 'e', $normalized);
        $normalized = str_replace('í', 'i', $normalized);
        $normalized = str_replace('ú', 'u', $normalized);
        $normalized = str_replace('ã', 'a', $normalized);
        $normalized = str_replace('õ', 'o', $normalized);
        $normalized = str_replace('ç', 'c', $normalized);
        $normalized = str_replace('lamentacoes de jeremias', 'lamentacoes', $normalized);
        $normalized = str_replace('canticos', 'canticos', $normalized);

        return $normalized;
    }
}
