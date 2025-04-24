<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Services\SlugService;

Route::get('/', function () {
    return Inertia::render('welcome');
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

// Bible Explanation Page Route
Route::get('/explicacao/{testamento}/{livro}/{capitulo}', function (string $testamento, string $livro, string $capitulo) {
    // Converter o slug para o nome original do livro
    $livroOriginal = SlugService::slugParaLivro($livro);
    
    return Inertia::render('explicacao/index', [
        'testamento' => $testamento,
        'livro' => $livroOriginal,
        'capitulo' => $capitulo
    ]);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
