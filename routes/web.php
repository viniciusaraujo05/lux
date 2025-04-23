<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

// Bible Explanation API Route
Route::get('/api/explanation/{testament}/{book}/{chapter}', [\App\Http\Controllers\BibleExplanationController::class, 'getExplanation']);

// Bible Explanation Page Route
Route::get('/explicacao/{testamento}/{livro}/{capitulo}', function (string $testamento, string $livro, string $capitulo) {
    return Inertia::render('explicacao/index', [
        'testamento' => $testamento,
        'livro' => $livro,
        'capitulo' => $capitulo
    ]);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
