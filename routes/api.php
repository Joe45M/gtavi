<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\MicrositeDataController;
use App\Http\Controllers\Api\V1\PostsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/posts', [PostsController::class, 'index'])->name('posts.index');
    Route::get('/posts/{slug}', [PostsController::class, 'show'])->name('posts.show');
    Route::get('/editions', [MicrositeDataController::class, 'editions'])->name('editions.index');
    Route::get('/platforms', [MicrositeDataController::class, 'platforms'])->name('platforms.index');
    Route::get('/milestones', [MicrositeDataController::class, 'milestones'])->name('milestones.index');
    Route::get('/locations', [MicrositeDataController::class, 'locations'])->name('locations.index');
});
