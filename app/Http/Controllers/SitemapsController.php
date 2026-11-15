<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\MicrositeDataService;
use Illuminate\Http\Response;

final readonly class SitemapsController
{
    public function sitemapXml(MicrositeDataService $data): Response
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');

        $staticPages = [
            ['loc' => $siteUrl.'/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => $siteUrl.'/release-info', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => $siteUrl.'/prices-editions', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => $siteUrl.'/download-sizes', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => $siteUrl.'/calculators', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => $siteUrl.'/map-atlas', 'priority' => '0.85', 'changefreq' => 'weekly'],
            ['loc' => $siteUrl.'/gta', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => $siteUrl.'/disclaimer', 'priority' => '0.5', 'changefreq' => 'monthly'],
        ];

        $posts = Post::query()->published()->orderByDesc('updated_at')->get(['slug', 'updated_at', 'published_at']);
        $categories = $data->categories();
        $tags = $data->tags();

        $xml = view('seo.sitemap', [
            'siteUrl' => $siteUrl,
            'staticPages' => $staticPages,
            'posts' => $posts,
            'categories' => $categories,
            'tags' => $tags,
        ])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robotsTxt(): Response
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');

        $content = implode("\n", [
            '# GTAVI.PRO — Independent GTA VI Microsite & Telemetry Blog',
            '# Not affiliated with Rockstar Games or Take-Two Interactive.',
            'User-agent: *',
            'Allow: /',
            'Disallow: /studio',
            'Disallow: /studio/',
            '',
            "Sitemap: {$siteUrl}/sitemap.xml",
            '',
        ]);

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function llmsTxt(MicrositeDataService $data): Response
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');
        $posts = Post::query()->published()->orderByDesc('published_at')->get(['title', 'slug', 'excerpt']);

        $lines = [
            '# GTAVI.PRO',
            '> Independent GTA VI fan microsite, hardware telemetry toolkit, and technical blog.',
            '> Legal Disclaimer: GTAVI.PRO is NOT affiliated with, endorsed by, or connected to Rockstar Games or Take-Two Interactive Software, Inc.',
            '',
            '## Core Microsite Hubs',
            "- [Home]({$siteUrl}/): Main hub with key telemetry, calculators, and dispatches.",
            "- [Release Info]({$siteUrl}/release-info): Verified Rockstar Games & Take-Two filings timeline.",
            "- [Prices & Editions]({$siteUrl}/prices-editions): Multi-currency projected SKU comparison.",
            "- [Download Sizes & Specs]({$siteUrl}/download-sizes): Storage and platform hardware projections.",
            "- [Interactive Calculators]({$siteUrl}/calculators): Download time, PC FPS, edition value, and Leonida map scale.",
            "- [Leonida Map Atlas]({$siteUrl}/map-atlas): Biome breakdown across Vice City, Keys, Grassrivers, and Port Gellhorn.",
            "- [Disclaimer]({$siteUrl}/disclaimer): Independence and non-affiliation notice.",
            '',
            '## Published Technical Articles',
        ];

        foreach ($posts as $post) {
            $lines[] = "- [{$post->title}]({$siteUrl}/gta/{$post->slug}): {$post->excerpt}";
        }

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
