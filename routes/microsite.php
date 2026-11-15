<?php

declare(strict_types=1);

use App\Http\Controllers\CalculatorsController;
use App\Http\Controllers\PagesController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', [PagesController::class, 'home'])->name('home');
Route::get('/release-info', [PagesController::class, 'releaseInfo'])->name('release-info');
Route::get('/prices-editions', [PagesController::class, 'pricesEditions'])->name('prices-editions');
Route::get('/editions', fn (): RedirectResponse => redirect()->route('prices-editions', status: 301));
Route::get('/download-sizes', [PagesController::class, 'downloadSizes'])->name('download-sizes');
Route::get('/calculators', [PagesController::class, 'calculators'])->name('calculators');
Route::post('/calculators/download-estimate', [CalculatorsController::class, 'downloadEstimate'])
    ->name('calculators.download-estimate');
Route::get('/map-atlas', [PagesController::class, 'mapAtlas'])->name('map-atlas');
Route::get('/map', fn (): RedirectResponse => redirect()->route('map-atlas', status: 301));
Route::get('/disclaimer', [PagesController::class, 'disclaimer'])->name('disclaimer');
