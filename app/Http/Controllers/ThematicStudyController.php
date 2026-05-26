<?php

namespace App\Http\Controllers;

use App\Services\ThematicStudyService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ThematicStudyController extends Controller
{
    public function __construct(private ThematicStudyService $themeService) {}

    public function index()
    {
        $topics = $this->themeService->allTopics();
        $title = 'Versículos sobre temas bíblicos | Verso a verso';
        $description = 'Encontre versículos sobre fé, confiança, oração, ansiedade, amor, família e outros temas bíblicos com explicações verso a verso.';

        return Inertia::render('temas/index', [
            'topics' => $topics,
        ])->withViewData([
            'title' => $title,
            'description' => $description,
            'keywords' => 'versículos sobre, temas bíblicos, versiculos biblicos, estudo bíblico por tema, explicação bíblica',
            'canonicalUrl' => url('/temas'),
            'robots' => 'index, follow',
            'jsonLd' => $this->collectionJsonLd($topics, $title, $description),
        ]);
    }

    public function show(string $slug)
    {
        $data = $this->themeService->getStudy($slug, false);
        $topic = $data['topic'];
        $study = $data['study'];
        $title = $topic['title'].' bíblicos explicados | Verso a verso';
        $description = $topic['description'].' Leia passagens selecionadas e abra a explicação completa de cada versículo.';

        return Inertia::render('temas/show', [
            'topic' => $topic,
            'study' => $study,
            'origin' => $data['origin'],
            'studyId' => $data['id'],
            'topics' => $this->themeService->allTopics(),
        ])->withViewData([
            'title' => $title,
            'description' => $description,
            'keywords' => $topic['keywords'],
            'canonicalUrl' => url('/temas/'.$topic['slug']),
            'robots' => 'index, follow',
            'ogType' => 'article',
            'jsonLd' => $this->articleJsonLd($topic, $study, $description),
        ]);
    }

    public function generate(string $slug, Request $request)
    {
        $data = $this->themeService->getStudy($slug, true);

        return response()->json($data);
    }

    private function collectionJsonLd(array $topics, string $title, string $description): string
    {
        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $title,
            'description' => $description,
            'url' => url('/temas'),
            'hasPart' => array_map(fn ($topic) => [
                '@type' => 'Article',
                'name' => $topic['title'],
                'url' => url('/temas/'.$topic['slug']),
            ], $topics),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function articleJsonLd(array $topic, array $study, string $description): string
    {
        $breadcrumbs = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Temas bíblicos', 'item' => url('/temas')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $topic['title'], 'item' => url('/temas/'.$topic['slug'])],
            ],
        ];

        $article = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $topic['title'].' bíblicos explicados',
            'description' => $description,
            'articleSection' => 'Versículos por tema',
            'about' => $topic['term'],
            'articleBody' => trim(($study['introducao'] ?? '').' '.($study['significado_biblico'] ?? '')),
            'mainEntityOfPage' => url('/temas/'.$topic['slug']),
            'inLanguage' => 'pt-BR',
            'author' => ['@type' => 'Organization', 'name' => 'Verso a verso'],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Verso a verso',
                'logo' => ['@type' => 'ImageObject', 'url' => asset('logo.svg')],
            ],
        ];

        return json_encode([$breadcrumbs, $article], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
