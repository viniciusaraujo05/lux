<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Services\SlugService;
use App\Http\Controllers\SeoController;

Route::get('/', function () {
    // Adicionar metadados de SEO específicos para a página inicial
    $seoData = [
        'title' => 'Verso a verso - Bíblia Explicada | Estudos Bíblicos Detalhados e Acessíveis',
        'description' => 'Verso a verso oferece explicações bíblicas detalhadas com análise teológica profunda, contexto histórico e aplicações práticas para cada capítulo e versículo da Bíblia.',
        'keywords' => 'Bíblia, estudo bíblico, explicação bíblica, teologia, versículos, análise, contexto histórico'
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

// API para obter metadados SEO para uma página específica
Route::get('/api/seo/{testament}/{book}/{chapter}', function (string $testament, string $book, string $chapter, \Illuminate\Http\Request $request) {
    // Converter o slug para o nome original do livro
    $bookOriginal = SlugService::slugParaLivro($book);
    $verses = $request->query('verses');
    
    // Gerar metadados SEO para esta página
    $title = ucfirst($bookOriginal) . ' ' . $chapter;
    if ($verses) {
        $title .= ':' . $verses;
    }
    $title .= ' - Explicação Bíblica | Verso a verso';
    
    $description = 'Estudo detalhado de ' . ucfirst($bookOriginal) . ' ' . $chapter;
    if ($verses) {
        $description .= ':' . $verses;
    }
    $description .= ' com contexto histórico, análise teológica e aplicações práticas. Entenda a Bíblia profundamente.';
    
    $keywords = 'Bíblia, ' . ucfirst($bookOriginal) . ', ' . $chapter;
    if ($verses) {
        $keywords .= ', versículo ' . str_replace(',', ', versículo ', $verses);
    }
    $keywords .= ', explicação bíblica, estudo bíblico, teologia, contexto histórico';
    
    return response()->json([
        'title' => $title,
        'description' => $description,
        'keywords' => $keywords
    ]);
});

// Feedback API Routes
Route::post('/api/feedback', [\App\Http\Controllers\ExplanationFeedbackController::class, 'store']);
Route::get('/api/feedback/stats/{id}', [\App\Http\Controllers\ExplanationFeedbackController::class, 'getStats']);

// Bible Navigation Routes
Route::get('/biblia', function () {
    return Inertia::render('welcome');
});

Route::get('/biblia/{testamento}/{livro}', function (string $testamento, string $livro) {
    // Converter o slug para o nome original do livro
    $livroOriginal = SlugService::slugParaLivro($livro);
    
    return Inertia::render('welcome', [
        'testamento' => $testamento,
        'livro' => $livroOriginal
    ]);
});

Route::get('/biblia/{testamento}/{livro}/{capitulo}', function (string $testamento, string $livro, string $capitulo) {
    // Converter o slug para o nome original do livro
    $livroOriginal = SlugService::slugParaLivro($livro);
    
    return Inertia::render('welcome', [
        'testamento' => $testamento,
        'livro' => $livroOriginal,
        'capitulo' => $capitulo
    ]);
});

// SEO Routes
Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);
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
        'relatedLinks' => $relatedLinks
    ]);
});

// Rotas para páginas institucionais
Route::get('/sobre', function () {
    return Inertia::render('about');
})->name('about');

Route::get('/faq', function () {
    return Inertia::render('faq');
})->name('faq');

// Bible Explanation Page Routes
Route::get('/explicacao/{testamento}/{livro}/{capitulo}', function (string $testamento, string $livro, string $capitulo) {
    // Converter o slug para o nome original do livro
    $livroOriginal = SlugService::slugParaLivro($livro);
    
    return Inertia::render('explicacao/index', [
        'testamento' => $testamento,
        'livro' => $livroOriginal,
        'capitulo' => $capitulo
    ]);
});

// URL amigável para SEO com slug - rota para versículos específicos
Route::get('/explicacao/{testamento}/{livro}/{capitulo}/{slug}', function (string $testamento, string $livro, string $capitulo, string $slug) {
    // Converter o slug para o nome original do livro
    $livroOriginal = SlugService::slugParaLivro($livro);
    
    // Extrair os versículos do slug (normalmente o primeiro segmento antes do primeiro hífen)
    $versesMatch = [];
    if (preg_match('/^(\d+(?:-\d+)?)/', $slug, $versesMatch)) {
        $verses = $versesMatch[1];
        
        return Inertia::render('explicacao/index', [
            'testamento' => $testamento,
            'livro' => $livroOriginal,
            'capitulo' => $capitulo,
            'versos' => $verses
        ]);
    }
    
    // Se não houver versículos no slug, é uma explicação de capítulo completo
    return Inertia::render('explicacao/index', [
        'testamento' => $testamento,
        'livro' => $livroOriginal,
        'capitulo' => $capitulo
    ]);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
