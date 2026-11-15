<?php

declare(strict_types=1);

use App\Http\Controllers\StudiosController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role_or_permission:admin|editor|access studio'])->group(function (): void {
    Route::get('/admin', [StudiosController::class, 'index'])->name('admin.index');

    Route::prefix('studio')->name('studio.')->group(function (): void {
        Route::get('/', [StudiosController::class, 'index'])->name('index');
        Route::get('/create', [StudiosController::class, 'create'])
            ->middleware('permission:create posts')
            ->name('create');
        Route::post('/', [StudiosController::class, 'store'])
            ->middleware('permission:create posts')
            ->name('store');
        Route::get('/{post}/edit', [StudiosController::class, 'edit'])
            ->middleware('permission:edit posts')
            ->name('edit');
        Route::put('/{post}', [StudiosController::class, 'update'])
            ->middleware('permission:edit posts')
            ->name('update');
        Route::delete('/{post}', [StudiosController::class, 'destroy'])
            ->middleware('permission:delete posts')
            ->name('destroy');
    });
});
