<?php

namespace App\Http\Controllers;

use App\Services\StaticContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AmpController extends Controller
{
    protected $staticContentService;

    public function __construct(StaticContentService $staticContentService)
    {
        $this->staticContentService = $staticContentService;
    }

    /**
     * Mostrar versão AMP da explicação bíblica (Conteúdo Estático para Indexação)
     */
    public function showExplanation($testament, $book, $chapter, ?string $slug = null, Request $request)
    {
        $verses = $request->query('verses');
        // Se temos um slug (ex: 8-explicacao-biblica), tentar extrair o verso se necessário
        if ($slug && !$verses && preg_match('/^(\d+)/', $slug, $m)) {
            $verses = $m[1];
        }

        // Usar o serviço de conteúdo estático (rápido e confiável para bots/AMP)
        $data = $this->staticContentService->getExplanationFallback($testament, $book, (int)$chapter, $verses);

        $canonicalUrl = url("/explicacao/{$testament}/{$book}/{$chapter}");
        if ($verses) {
            $versesSlug = Str::slug((string) $verses);
            $canonicalUrl = url("/explicacao/{$testament}/{$book}/{$chapter}/{$versesSlug}-explicacao-biblica");
        }

        return view('amp.explanation', [
            'testament' => $testament,
            'book' => $data['book'],
            'chapter' => $data['chapter'],
            'verses' => $verses,
            'title' => $data['title'],
            'description' => $data['description'],
            'keywords' => $data['keywords'],
            'intro' => $data['intro'] ?? '',
            'content' => $data['content'] ?? '',
            'sections' => $data['sections'] ?? [],
            'canonicalUrl' => $canonicalUrl,
            'relatedLinks' => $data['related_links'] ?? [],
        ]);
    }
}
