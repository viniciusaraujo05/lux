<?php

use App\Http\Controllers\SeoController;
use App\Services\BibleTextService;
use App\Services\ExplanationQueryService;
use App\Services\SlugService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

$normalizeBibleTestament = static function (string $testamento): ?string {
    return match (strtolower($testamento)) {
        'velho', 'antigo' => 'velho',
        'novo' => 'novo',
        default => null,
    };
};

$findExistingExplanation = static function (string $testamento, string $livroOriginal, int $capitulo, ?string $verses, string $version): ?array {
    $existing = app(ExplanationQueryService::class)->findForDisplay($testamento, $livroOriginal, $capitulo, $verses, $version);
    if (! $existing) {
        return null;
    }

    $decoded = json_decode($existing->explanation_text, true);

    return [
        'explanation' => json_last_error() === JSON_ERROR_NONE ? $decoded : $existing->explanation_text,
        'origin' => $existing->source ?? 'database',
        'id' => $existing->id,
    ];
};

$getChapterVerses = static function (string $version, string $livroSlug, int $capitulo): array {
    try {
        return app(BibleTextService::class)->getChapter($version, $livroSlug, $capitulo)['verses'] ?? [];
    } catch (\Throwable) {
        return [];
    }
};

$getVerseSnippet = static function (array $chapterVerses, ?string $verses): ?string {
    if (! $verses || str_contains($verses, ',') || str_contains($verses, '-')) {
        return null;
    }

    $verseNumber = (int) $verses;
    foreach ($chapterVerses as $verse) {
        if ((int) ($verse['number'] ?? 0) === $verseNumber) {
            $text = trim((string) ($verse['text'] ?? ''));

            return $text !== '' ? \Illuminate\Support\Str::limit($text, 150, '') : null;
        }
    }

    return null;
};

Route::get('/', function () {
    // Adicionar metadados de SEO específicos para a página inicial
    $seoData = [
        'title' => 'Verso a verso - Bíblia Explicada | Estudos Bíblicos Detalhados e Acessíveis',
        'description' => 'Verso a verso oferece explicações bíblicas detalhadas com análise teológica profunda, contexto histórico e aplicações práticas para cada capítulo e versículo da Bíblia.',
        'keywords' => 'Bíblia, estudo bíblico, explicação bíblica, teologia, versículos, análise, contexto histórico',
    ];

    return Inertia::render('welcome', $seoData);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

// Bible Explanation API Route
Route::get('/api/explanation/{testament}/{book}/{chapter}', function (string $testament, string $book, string $chapter, \Illuminate\Http\Request $request) {
    // Converter o slug para o nome original do livro
    $bookOriginal = SlugService::slugParaLivro($book);

    // Encaminhar para o controlador com o nome original do livro
    return app()->make(\App\Http\Controllers\BibleExplanationController::class)
        ->getExplanation($request, $testament, $bookOriginal, $chapter);
});

// Bible Explanation Streaming API Route (SSE)
Route::get('/api/explanation-stream/{testament}/{book}/{chapter}', function (string $testament, string $book, string $chapter, \Illuminate\Http\Request $request) {
    // Converter o slug para o nome original do livro
    $bookOriginal = SlugService::slugParaLivro($book);

    // Encaminhar para o controlador de streaming com o nome original
    return app()->make(\App\Http\Controllers\StreamingExplanationController::class)
        ->stream($request, $testament, $bookOriginal, (int) $chapter);
});

// Book Context API Route
Route::get('/api/book-context/{testament}/{book}', function (string $testament, string $book, \Illuminate\Http\Request $request) {
    // Converter o slug para o nome original do livro
    $bookOriginal = SlugService::slugParaLivro($book);

    // Encaminhar para o controlador com o nome original do livro
    return app()->make(\App\Http\Controllers\BookContextController::class)
        ->getBookContext($request, $testament, $bookOriginal);
});

// API para obter metadados SEO para uma página específica
Route::get('/api/seo/{testament}/{book}/{chapter}', function (string $testament, string $book, string $chapter, \Illuminate\Http\Request $request) {
    // Converter o slug para o nome original do livro
    $bookOriginal = SlugService::slugParaLivro($book);
    $verses = $request->query('verses');

    // Gerar metadados SEO para esta página
    $title = ucfirst($bookOriginal).' '.$chapter;
    if ($verses) {
        $title .= ':'.$verses;
    }
    $title .= $verses
        ? ' - Versículo bíblico com explicação | Verso a verso'
        : ' - Bíblia comentada verso a verso | Verso a verso';

    $description = 'Leia '.ucfirst($bookOriginal).' '.$chapter;
    if ($verses) {
        $description .= ':'.$verses;
    }
    $description .= $verses
        ? ' e abra a explicação do versículo quando quiser.'
        : ' completo na Bíblia com explicações de capítulo e versículo sob demanda.';

    $keywords = 'Bíblia, '.ucfirst($bookOriginal).', '.$chapter;
    if ($verses) {
        $keywords .= ', versículo '.str_replace(',', ', versículo ', $verses);
    }
    $keywords .= ', bíblia online, texto bíblico, explicação bíblica, estudo bíblico';

    return response()->json([
        'title' => $title,
        'description' => $description,
        'keywords' => $keywords,
    ]);
});

// Feedback API Routes
Route::post('/api/feedback', [\App\Http\Controllers\ExplanationFeedbackController::class, 'store']);
Route::get('/api/feedback/stats/{id}', [\App\Http\Controllers\ExplanationFeedbackController::class, 'getStats']);
Route::get('/api/bible-text/{version}/{testament}/{book}/{chapter}', [\App\Http\Controllers\BibleTextController::class, 'getChapterText']);

// Bible Navigation Routes
Route::get('/biblia', function () {
    return Inertia::render('welcome');
});

Route::get('/biblia/{testamento}', function (string $testamento) use ($normalizeBibleTestament) {
    $normalizedTestament = $normalizeBibleTestament($testamento);
    if (! $normalizedTestament) {
        abort(404);
    }
    if ($testamento !== $normalizedTestament) {
        return redirect("/biblia/{$normalizedTestament}", 301);
    }

    return Inertia::render('welcome', [
        'testamento' => $normalizedTestament,
    ]);
});

Route::get('/biblia/{testamento}/{livro}', function (string $testamento, string $livro) use ($normalizeBibleTestament) {
    $normalizedTestament = $normalizeBibleTestament($testamento);
    if (! $normalizedTestament) {
        abort(404);
    }
    if ($testamento !== $normalizedTestament) {
        return redirect("/biblia/{$normalizedTestament}/{$livro}", 301);
    }

    // Converter o slug para o nome original do livro
    $livroOriginal = SlugService::slugParaLivro($livro);

    return Inertia::render('welcome', [
        'testamento' => $normalizedTestament,
        'livro' => $livroOriginal,
    ]);
});

Route::get('/biblia/{testamento}/{livro}/{capitulo}', function (string $testamento, string $livro, string $capitulo) use ($normalizeBibleTestament) {
    $normalizedTestament = $normalizeBibleTestament($testamento);
    if (! $normalizedTestament) {
        abort(404);
    }
    $explicacaoTestamento = $normalizedTestament === 'velho' ? 'antigo' : 'novo';

    return redirect("/explicacao/{$explicacaoTestamento}/{$livro}/{$capitulo}", 301);
});

// SEO Routes
Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);
Route::get('/sitemap-antigo-testamento.xml', [SeoController::class, 'sitemapAntigoTestamento']);
Route::get('/sitemap-novo-testamento.xml', [SeoController::class, 'sitemapNovoTestamento']);
Route::get('/sitemap-amp.xml', [SeoController::class, 'sitemapAmp']);
Route::get('/sitemap-principal.xml', [SeoController::class, 'sitemapPrincipal']);
Route::get('/robots.txt', [SeoController::class, 'robots']);

// AMP Routes
Route::get('/amp/explicacao/{testament}/{book}/{chapter}', [\App\Http\Controllers\AmpController::class, 'showExplanation']);
Route::get('/amp/explicacao/{testament}/{book}/{chapter}/{slug}', [\App\Http\Controllers\AmpController::class, 'showExplanation']);

// API para links relacionados
Route::get('/api/related/{testament}/{book}/{chapter}', function (string $testament, string $book, string $chapter, \Illuminate\Http\Request $request) {
    $bookOriginal = \App\Services\SlugService::slugParaLivro($book);
    $verses = $request->query('verses');

    // Instanciar o serviço de conteúdo relacionado
    $relatedContentService = app()->make(\App\Services\RelatedContentService::class);

    // Obter links relacionados
    $relatedLinks = $relatedContentService->getRelatedContent($testament, $bookOriginal, $chapter, $verses);

    return response()->json([
        'relatedLinks' => $relatedLinks,
    ]);
});

// Rotas para páginas institucionais
Route::get('/sobre', function () {
    return Inertia::render('about');
})->name('about');

Route::get('/faq', function () {
    return Inertia::render('faq');
})->name('faq');

// Página de ofertas/doações
Route::get('/ofertar', function () {
    return Inertia::render('ofertar');
})->name('ofertar');

// Book Context Page Routes
Route::get('/contexto/{testamento}/{livro}', function (string $testamento, string $livro, \Illuminate\Http\Request $request) {
    // Converter o slug para o nome original do livro
    $livroOriginal = SlugService::slugParaLivro($livro);

    // Prefetch context for SSR initial props only on full page loads (not Inertia visits)
    $prefetch = null;
    if (! $request->header('X-Inertia')) {
        $prefetch = app()->make(\App\Services\BookContextService::class)
            ->getBookContext($testamento, $livroOriginal);
    }

    // SEO meta dinâmico (contexto do livro)
    $title = 'Contexto de '.ucfirst($livroOriginal).' - Análise Bíblica | Verso a verso';
    $description = 'Análise completa do contexto bíblico de '.ucfirst($livroOriginal).' com gênero literário, contexto histórico, autoria, doutrinas e aplicação contemporânea.';
    $keywords = implode(', ', [
        'contexto bíblico',
        'análise '.$livroOriginal,
        'gênero literário',
        'contexto histórico',
        'autoria bíblica',
        'doutrinas',
        'estudo bíblico',
        'teologia',
    ]);
    $canonicalUrl = url("/contexto/{$testamento}/{$livro}");
    $bibleTestamento = $testamento === 'antigo' ? 'velho' : 'novo';

    $breadcrumbs = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Bíblia', 'item' => url('/biblia')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => ucfirst($testamento), 'item' => url("/biblia/{$bibleTestamento}")],
            ['@type' => 'ListItem', 'position' => 4, 'name' => $livroOriginal, 'item' => url("/biblia/{$bibleTestamento}/{$livro}")],
            ['@type' => 'ListItem', 'position' => 5, 'name' => 'Contexto', 'item' => $canonicalUrl],
        ],
    ];
    $article = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => 'Contexto de '.$livroOriginal,
        'mainEntityOfPage' => $canonicalUrl,
        'inLanguage' => 'pt-BR',
        'author' => ['@type' => 'Organization', 'name' => 'Verso a verso'],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Verso a verso',
            'logo' => ['@type' => 'ImageObject', 'url' => asset('logo.svg')],
        ],
        'datePublished' => now()->toIso8601String(),
        'dateModified' => now()->toIso8601String(),
    ];
    $jsonLd = json_encode([$breadcrumbs, $article], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $pageProps = [
        'testamento' => $testamento,
        'livro' => $livroOriginal,
        // SSR initial props
        'initialContext' => $prefetch ?? null,
    ];

    return Inertia::render('contexto/index', $pageProps)->withViewData([
        'title' => $title,
        'description' => $description,
        'keywords' => $keywords,
        'canonicalUrl' => $canonicalUrl,
        'robots' => 'index, follow',
        'jsonLd' => $jsonLd,
        'ogType' => 'article',
    ]);
})->where('testamento', '^(antigo|novo)$');

// Bible Explanation Page Routes (capítulo)
Route::get('/explicacao/{testamento}/{livro}/{capitulo}', function (string $testamento, string $livro, string $capitulo, \Illuminate\Http\Request $request) use ($findExistingExplanation, $getChapterVerses, $getVerseSnippet) {
    // Converter o slug para o nome original do livro
    $livroOriginal = SlugService::slugParaLivro($livro);

    // Versículos por query param (suporta SSR canônico com ?verses=)
    $verses = $request->query('verses');
    $version = strtolower((string) $request->query('version', 'nvi'));
    $chapterVerses = $getChapterVerses($version, $livro, (int) $capitulo);
    $verseSnippet = $getVerseSnippet($chapterVerses, $verses);

    // Fluxo novo: só carrega explicação existente; nunca gera IA no carregamento da página
    $prefetch = null;
    if (! $request->header('X-Inertia')) {
        $prefetch = $findExistingExplanation($testamento, $livroOriginal, (int) $capitulo, $verses ?: null, $version);
    }

    // SEO meta dinâmico (leitura primeiro, explicação sob demanda)
    $titleBase = ucfirst($livroOriginal).' '.$capitulo;
    if ($verses) {
        $titleBase .= ':'.$verses;
    }
    $title = $verses
        ? $titleBase.' - Versículo bíblico com explicação | Verso a verso'
        : $titleBase.' - Bíblia comentada verso a verso | Verso a verso';
    $description = $verses
        ? 'Leia '.$titleBase.($verseSnippet ? ' — '.$verseSnippet : '').'. Veja a explicação bíblica com contexto, sentido da passagem e aplicação prática.'
        : 'Leia '.ucfirst($livroOriginal).' '.$capitulo.' completo na Bíblia, escolha a versão e abra explicações do capítulo ou de cada versículo quando quiser.';
    $keywords = implode(', ', [
        'bíblia online',
        $titleBase,
        'texto bíblico',
        'explicação bíblica',
        'comentário '.$titleBase,
        'estudo bíblico',
        'versículo por versículo',
    ]);
    $canonicalUrl = $verses
        ? url("/explicacao/{$testamento}/{$livro}/{$capitulo}/{$verses}-explicacao-biblica")
        : url("/explicacao/{$testamento}/{$livro}/{$capitulo}");
    $bibleTestamento = $testamento === 'antigo' ? 'velho' : 'novo';

    $breadcrumbs = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Bíblia', 'item' => url('/biblia')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => ucfirst($testamento), 'item' => url("/biblia/{$bibleTestamento}")],
            ['@type' => 'ListItem', 'position' => 4, 'name' => $livroOriginal, 'item' => url("/biblia/{$bibleTestamento}/{$livro}")],
            ['@type' => 'ListItem', 'position' => 5, 'name' => 'Capítulo '.$capitulo, 'item' => url("/explicacao/{$testamento}/{$livro}/{$capitulo}")],
        ],
    ];
    if ($verses) {
        $breadcrumbs['itemListElement'][] = ['@type' => 'ListItem', 'position' => 6, 'name' => 'Verso '.$verses, 'item' => $canonicalUrl];
    }
    $article = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $verses ? 'Explicação de '.$titleBase : 'Leitura e estudo de '.$titleBase,
        'description' => $description,
        'about' => $titleBase,
        'articleSection' => $verses ? 'Explicação de versículo bíblico' : 'Leitura bíblica comentada',
        'articleBody' => $verseSnippet ?: ('Leia '.ucfirst($livroOriginal).' '.$capitulo.' completo com explicações bíblicas sob demanda.'),
        'mainEntityOfPage' => $canonicalUrl,
        'inLanguage' => 'pt-BR',
        'author' => ['@type' => 'Organization', 'name' => 'Verso a verso'],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Verso a verso',
            'logo' => ['@type' => 'ImageObject', 'url' => asset('logo.svg')],
        ],
        'datePublished' => now()->toIso8601String(),
        'dateModified' => now()->toIso8601String(),
    ];
    $jsonLd = json_encode([$breadcrumbs, $article], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $pageProps = [
        'testamento' => $testamento,
        'livro' => $livroOriginal,
        'capitulo' => $capitulo,
        // SSR initial props
        'initialExplanation' => $prefetch['explanation'] ?? null,
        'initialSource' => $prefetch['origin'] ?? 'unknown',
        'initialExplanationId' => $prefetch['id'] ?? null,
        'version' => $version,
        'initialChapterVerses' => $chapterVerses,
    ];
    if (! empty($verses)) {
        $pageProps['versos'] = $verses;
    }

    return Inertia::render('explicacao/index', $pageProps)->withViewData([
        'title' => $title,
        'description' => $description,
        'keywords' => $keywords,
        'canonicalUrl' => $canonicalUrl,
        'robots' => 'index, follow',
        'jsonLd' => $jsonLd,
        'ogType' => 'article',
        'ampUrl' => $verses
            ? url("/amp/explicacao/{$testamento}/{$livro}/{$capitulo}/{$verses}-explicacao-biblica")
            : url("/amp/explicacao/{$testamento}/{$livro}/{$capitulo}"),
    ]);
})->where('testamento', '^(antigo|novo)$');

// URL amigável para SEO com slug - rota para versículos específicos
Route::get('/explicacao/{testamento}/{livro}/{capitulo}/{slug}', function (string $testamento, string $livro, string $capitulo, string $slug, \Illuminate\Http\Request $request) use ($findExistingExplanation, $getChapterVerses, $getVerseSnippet) {
    // Converter o slug para o nome original do livro
    $livroOriginal = SlugService::slugParaLivro($livro);
    $version = strtolower((string) $request->query('version', 'nvi'));
    $chapterVerses = $getChapterVerses($version, $livro, (int) $capitulo);

    // Extrair os versículos do slug (normalmente o primeiro segmento antes do primeiro hífen)
    // Normalizar slug para capturar formatos como "5:5", "5-5", "5"
    $normalizedSlug = str_replace(['%3A', ':', '%2F', '/'], '-', $slug);
    if (preg_match('/^(\d+)(?:[-](\d+))?/', $normalizedSlug, $versesMatch)) {
        $verses = isset($versesMatch[2]) ? ($versesMatch[1].'-'.$versesMatch[2]) : $versesMatch[1];

        // Fluxo novo: só carrega explicação existente; nunca gera IA no carregamento da página
        $prefetch = null;
        if (! $request->header('X-Inertia')) {
            $prefetch = $findExistingExplanation($testamento, $livroOriginal, (int) $capitulo, $verses, $version);
        }
        // SEO meta dinâmico (versículo)
        $titleBase = ucfirst($livroOriginal).' '.$capitulo.':'.$verses;
        $verseSnippet = $getVerseSnippet($chapterVerses, $verses);
        $title = $titleBase.' - Explicação bíblica do versículo | Verso a verso';
        $description = 'Leia '.$titleBase.($verseSnippet ? ' — '.$verseSnippet : '').'. Veja a explicação bíblica do versículo com contexto, sentido da passagem e aplicação prática.';
        $keywords = implode(', ', [
            'explicação bíblica',
            $titleBase,
            'comentário '.$titleBase,
            'estudo bíblico',
            'contexto histórico',
            'teologia',
        ]);
        $canonicalUrl = url("/explicacao/{$testamento}/{$livro}/{$capitulo}/{$verses}-explicacao-biblica");
        $bibleTestamento = $testamento === 'antigo' ? 'velho' : 'novo';

        $breadcrumbs = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Bíblia', 'item' => url('/biblia')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => ucfirst($testamento), 'item' => url("/biblia/{$bibleTestamento}")],
                ['@type' => 'ListItem', 'position' => 4, 'name' => $livroOriginal, 'item' => url("/biblia/{$bibleTestamento}/{$livro}")],
                ['@type' => 'ListItem', 'position' => 5, 'name' => 'Capítulo '.$capitulo, 'item' => url("/explicacao/{$testamento}/{$livro}/{$capitulo}")],
                ['@type' => 'ListItem', 'position' => 6, 'name' => 'Verso(s) '.$verses, 'item' => $canonicalUrl],
            ],
        ];
        $article = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => 'Explicação de '.$titleBase,
            'description' => $description,
            'about' => $titleBase,
            'articleSection' => 'Explicação de versículo bíblico',
            'articleBody' => $verseSnippet ?: 'Página de explicação bíblica do versículo com leitura, contexto e aplicação prática.',
            'mainEntityOfPage' => $canonicalUrl,
            'inLanguage' => 'pt-BR',
            'author' => ['@type' => 'Organization', 'name' => 'Verso a verso'],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Verso a verso',
                'logo' => ['@type' => 'ImageObject', 'url' => asset('logo.svg')],
            ],
            'datePublished' => now()->toIso8601String(),
            'dateModified' => now()->toIso8601String(),
        ];
        $jsonLd = json_encode([$breadcrumbs, $article], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return Inertia::render('explicacao/index', [
            'testamento' => $testamento,
            'livro' => $livroOriginal,
            'capitulo' => $capitulo,
            'versos' => $verses,
            // SSR initial props only on full page loads
            'initialExplanation' => $prefetch['explanation'] ?? null,
            'initialSource' => $prefetch['origin'] ?? 'unknown',
            'initialExplanationId' => $prefetch['id'] ?? null,
            'version' => $version,
            'initialChapterVerses' => $chapterVerses,
        ])->withViewData([
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonicalUrl' => $canonicalUrl,
            'robots' => 'index, follow',
            'jsonLd' => $jsonLd,
            'ampUrl' => url("/amp/explicacao/{$testamento}/{$livro}/{$capitulo}/{$verses}-explicacao-biblica"),
        ]);
    }

    // Se não houver versículos no slug, é uma explicação de capítulo completo
    $titleBase = ucfirst($livroOriginal).' '.$capitulo;
    $title = $titleBase.' - Bíblia comentada verso a verso | Verso a verso';
    $description = 'Leia '.$titleBase.' completo na Bíblia, escolha a versão e abra explicações do capítulo ou de cada versículo quando quiser.';
    $keywords = implode(', ', [
        'explicação bíblica',
        $titleBase,
        'comentário '.$titleBase,
        'estudo bíblico',
        'contexto histórico',
        'teologia',
    ]);
    $canonicalUrl = url("/explicacao/{$testamento}/{$livro}/{$capitulo}");
    $bibleTestamento = $testamento === 'antigo' ? 'velho' : 'novo';

    $breadcrumbs = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Bíblia', 'item' => url('/biblia')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => ucfirst($testamento), 'item' => url("/biblia/{$bibleTestamento}")],
            ['@type' => 'ListItem', 'position' => 4, 'name' => $livroOriginal, 'item' => url("/biblia/{$bibleTestamento}/{$livro}")],
            ['@type' => 'ListItem', 'position' => 5, 'name' => 'Capítulo '.$capitulo, 'item' => url("/explicacao/{$testamento}/{$livro}/{$capitulo}")],
        ],
    ];
    $article = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => 'Explicação de '.$titleBase,
        'description' => $description,
        'about' => $titleBase,
        'articleSection' => 'Leitura bíblica comentada',
        'articleBody' => 'Leia '.$titleBase.' completo com explicações bíblicas sob demanda por capítulo ou versículo.',
        'mainEntityOfPage' => $canonicalUrl,
        'inLanguage' => 'pt-BR',
        'author' => ['@type' => 'Organization', 'name' => 'Verso a verso'],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Verso a verso',
            'logo' => ['@type' => 'ImageObject', 'url' => asset('logo.svg')],
        ],
        'datePublished' => now()->toIso8601String(),
        'dateModified' => now()->toIso8601String(),
    ];
    $jsonLd = json_encode([$breadcrumbs, $article], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // Fluxo novo: só carrega explicação existente; nunca gera IA no carregamento da página
    $prefetch = null;
    if (! $request->header('X-Inertia')) {
        $prefetch = $findExistingExplanation($testamento, $livroOriginal, (int) $capitulo, null, $version);
    }

    return Inertia::render('explicacao/index', [
        'testamento' => $testamento,
        'livro' => $livroOriginal,
        'capitulo' => $capitulo,
        // SSR initial props
        'initialExplanation' => $prefetch['explanation'] ?? null,
        'initialSource' => $prefetch['origin'] ?? 'unknown',
        'initialExplanationId' => $prefetch['id'] ?? null,
        'version' => $version,
        'initialChapterVerses' => $chapterVerses,
    ])->withViewData([
        'title' => $title,
        'description' => $description,
        'keywords' => $keywords,
        'canonicalUrl' => $canonicalUrl,
        'robots' => 'index, follow',
        'jsonLd' => $jsonLd,
        'ampUrl' => url("/amp/explicacao/{$testamento}/{$livro}/{$capitulo}"),
    ]);
})->where('testamento', '^(antigo|novo)$');

// Rota alternativa para capturar buscas tipo "mateus 5:5 explicação" e redirecionar para a URL canônica
Route::get('/explicacao/{livro}/{capitulo}/{slug?}', function (string $livro, string $capitulo, ?string $slug = null) {
    $lower = strtolower($livro);
    $nt = [
        'mateus', 'marcos', 'lucas', 'joao', 'atos', 'romanos', '1corintios', '2corintios', 'galatas', 'efesios', 'filipenses', 'colossenses', '1tessalonicenses', '2tessalonicenses', '1timoteo', '2timoteo', 'tito', 'filemom', 'hebreus', 'tiago', '1pedro', '2pedro', '1joao', '2joao', '3joao', 'judas', 'apocalipse',
    ];
    $testamento = in_array($lower, $nt) ? 'novo' : 'antigo';
    $target = "/explicacao/{$testamento}/{$livro}/{$capitulo}".($slug ? "/{$slug}" : '');

    return redirect($target, 301);
})->where([
    'livro' => '^(?!antigo$|novo$).+',
    'capitulo' => '[0-9]+',
]);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
