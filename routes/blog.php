<?php

declare(strict_types=1);

use App\Http\Controllers\PostsController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::prefix('gta')->name('blog.')->group(function (): void {
    Route::get('/', [PostsController::class, 'index'])->name('index');
    Route::get('/category/{slug}', [PostsController::class, 'category'])->name('category');
    Route::get('/tag/{slug}', [PostsController::class, 'tag'])->name('tag');
    Route::get('/{slug}', [PostsController::class, 'show'])->name('show');
});

Route::prefix('blog')->group(function (): void {
    Route::get('/', fn (): RedirectResponse => redirect()->route('blog.index', status: 301));
    Route::get('/category/{slug}', fn (string $slug): RedirectResponse => redirect()->route('blog.category', ['slug' => $slug], 301));
    Route::get('/tag/{slug}', fn (string $slug): RedirectResponse => redirect()->route('blog.tag', ['slug' => $slug], 301));
    Route::get('/{slug}', fn (string $slug): RedirectResponse => redirect()->route('blog.show', ['slug' => $slug], 301));
});
