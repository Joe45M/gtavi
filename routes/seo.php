<?php

declare(strict_types=1);

use App\Http\Controllers\SitemapsController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SitemapsController::class, 'sitemapXml'])->name('seo.sitemap');
Route::get('/robots.txt', [SitemapsController::class, 'robotsTxt'])->name('seo.robots');
Route::get('/llms.txt', [SitemapsController::class, 'llmsTxt'])->name('seo.llms');
Route::get('/llms-full.txt', [SitemapsController::class, 'llmsFullTxt'])->name('seo.llms-full');
Route::get('/feed.xml', [SitemapsController::class, 'rssFeed'])->name('seo.feed');
Route::get('/openapi.json', [SitemapsController::class, 'openApiJson'])->name('seo.openapi');
Route::get('/og/{slug}.svg', [SitemapsController::class, 'ogImageSvg'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('seo.og-image');
