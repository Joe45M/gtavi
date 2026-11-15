<?php

declare(strict_types=1);

use App\Http\Controllers\SitemapsController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SitemapsController::class, 'sitemapXml'])->name('seo.sitemap');
Route::get('/robots.txt', [SitemapsController::class, 'robotsTxt'])->name('seo.robots');
Route::get('/llms.txt', [SitemapsController::class, 'llmsTxt'])->name('seo.llms');
