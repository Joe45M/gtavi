<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\GameEdition;
use App\Models\MapLocation;
use App\Models\PlatformSpec;
use App\Models\Post;
use App\Models\ReleaseMilestone;
use App\Services\ContentBlockService;
use App\Services\MicrositeDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

final readonly class SitemapsController
{
    public function sitemapXml(MicrositeDataService $data): Response
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');
        $toAtom = static fn (mixed $timestamp): string => $timestamp
            ? Carbon::parse((string) $timestamp)->toAtomString()
            : now()->toAtomString();

        $latestPostAt = Post::query()->published()->max('updated_at');
        $latestMilestoneAt = ReleaseMilestone::query()->max('updated_at');
        $latestEditionAt = GameEdition::query()->max('updated_at');
        $latestPlatformAt = PlatformSpec::query()->max('updated_at');
        $latestMapAt = MapLocation::query()->max('updated_at');
        $latestGlobalAt = collect([$latestPostAt, $latestMilestoneAt, $latestEditionAt, $latestPlatformAt, $latestMapAt])
            ->filter()
            ->max();

        $staticPages = [
            ['loc' => $siteUrl.'/', 'lastmod' => $toAtom($latestGlobalAt), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => $siteUrl.'/release-info', 'lastmod' => $toAtom($latestMilestoneAt ?? $latestPlatformAt), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => $siteUrl.'/prices-editions', 'lastmod' => $toAtom($latestEditionAt), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => $siteUrl.'/download-sizes', 'lastmod' => $toAtom($latestPlatformAt), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => $siteUrl.'/calculators', 'lastmod' => $toAtom($latestGlobalAt), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => $siteUrl.'/map-atlas', 'lastmod' => $toAtom($latestMapAt), 'priority' => '0.85', 'changefreq' => 'weekly'],
            ['loc' => $siteUrl.'/gta', 'lastmod' => $toAtom($latestPostAt), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => $siteUrl.'/disclaimer', 'lastmod' => $toAtom($latestGlobalAt), 'priority' => '0.5', 'changefreq' => 'monthly'],
        ];

        $posts = Post::query()
            ->published()
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at', 'published_at']);

        $categories = $data->categories()
            ->filter(fn ($category): bool => (int) ($category->posts_count ?? 0) > 0)
            ->values();

        $tags = $data->tags()
            ->filter(fn ($tag): bool => (int) ($tag->posts_count ?? 0) > 0)
            ->values();

        $xml = view('seo.sitemap', [
            'siteUrl' => $siteUrl,
            'staticPages' => $staticPages,
            'posts' => $posts,
            'categories' => $categories,
            'tags' => $tags,
            'archiveLastmod' => $toAtom($latestPostAt),
        ])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }

    public function robotsTxt(): Response
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');

        $crawlers = [
            '*',
            'Googlebot',
            'Bingbot',
            'OAI-SearchBot',
            'ChatGPT-User',
            'GPTBot',
            'PerplexityBot',
            'ClaudeBot',
            'Claude-SearchBot',
            'Google-Extended',
            'Applebot-Extended',
        ];

        $lines = [
            '# GTAVI.PRO — Independent GTA VI Microsite & Telemetry Blog',
            '# Not affiliated with Rockstar Games or Take-Two Interactive.',
            '',
        ];

        foreach ($crawlers as $agent) {
            $lines[] = "User-agent: {$agent}";
            $lines[] = 'Allow: /';
            $lines[] = 'Allow: /llms.txt';
            $lines[] = 'Allow: /llms-full.txt';
            $lines[] = 'Allow: /api/v1/';
            $lines[] = 'Disallow: /studio';
            $lines[] = 'Disallow: /studio/';
            $lines[] = 'Disallow: /admin';
            $lines[] = 'Disallow: /login';
            $lines[] = 'Disallow: /logout';
            $lines[] = '';
        }

        $lines[] = "Sitemap: {$siteUrl}/sitemap.xml";
        $lines[] = '';

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function llmsTxt(MicrositeDataService $data): Response
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');
        $posts = Post::query()
            ->published()
            ->with('category')
            ->orderByDesc('published_at')
            ->get(['category_id', 'title', 'slug', 'excerpt', 'verification_badge', 'published_at', 'updated_at']);

        $lines = [
            '# GTAVI.PRO',
            '> Independent GTA VI fan microsite, hardware telemetry toolkit, and technical blog.',
            '> Legal Disclaimer: GTAVI.PRO is NOT affiliated with, endorsed by, or connected to Rockstar Games or Take-Two Interactive Software, Inc.',
            '',
            '## AI & Machine-Readable Ingestion Endpoints',
            "- [Complete Telemetry Corpus (Markdown)]({$siteUrl}/llms-full.txt): Single-request full corpus with release filings, pricing matrix, platform specs, Leonida map data, and full article markdown.",
            "- [OpenAPI 3.1 Specification]({$siteUrl}/openapi.json): Machine-readable schema for GTAVI.PRO public JSON endpoints and calculators.",
            "- [RSS 2.0 Syndication Feed]({$siteUrl}/feed.xml): Latest published technical dispatches.",
            "- [XML Sitemap]({$siteUrl}/sitemap.xml): Canonical URL index with ISO-8601 lastmod timestamps.",
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
            '## Public REST JSON API v1',
            "- [Published Articles JSON]({$siteUrl}/api/v1/posts)",
            "- [Editions Matrix JSON]({$siteUrl}/api/v1/editions)",
            "- [Platform Specs JSON]({$siteUrl}/api/v1/platforms)",
            "- [Release Milestones JSON]({$siteUrl}/api/v1/milestones)",
            "- [Leonida Map Locations JSON]({$siteUrl}/api/v1/locations)",
            '',
            '## Published Technical Articles (HTML & Clean Markdown Mirrors)',
        ];

        foreach ($posts as $post) {
            $badge = $post->verificationLabel();
            $lines[] = "- [{$post->title}]({$siteUrl}/gta/{$post->slug}) ([Markdown]({$siteUrl}/gta/{$post->slug}.md)) [{$badge}]: {$post->excerpt}";
        }

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }

    public function llmsFullTxt(MicrositeDataService $data, ContentBlockService $blocks): Response
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');
        $milestones = $data->milestones();
        $editions = $data->editions();
        $platforms = $data->platformSpecs();
        $locations = $data->mapLocations();
        $posts = Post::query()
            ->published()
            ->with(['category', 'tags'])
            ->orderByDesc('published_at')
            ->get();

        $sections = [
            implode("\n", [
                '# GTAVI.PRO — Complete AI & Telemetry Knowledge Corpus',
                '',
                '> Legal Disclaimer: GTAVI.PRO is an independent fan publication and technical telemetry desk. It is NOT affiliated with, endorsed by, or connected to Rockstar Games or Take-Two Interactive Software, Inc.',
                "> Canonical Domain: {$siteUrl}/",
                '> Verification Taxonomy: Confirmed Fact / Official Filing (direct Rockstar Newswire or Take-Two IR disclosures) vs. Technical Projection (independent modeling).',
            ]),
        ];

        // Release Milestones Table
        $milestoneLines = [
            '## 1. Verified Release Timeline & Projections',
            '',
            '| Date | Event | Status | Source | Summary |',
            '| --- | --- | --- | --- | --- |',
        ];
        foreach ($milestones as $milestone) {
            $status = $milestone->is_confirmed ? 'Confirmed' : 'Projected';
            $milestoneLines[] = sprintf(
                '| %s | %s | %s | %s | %s |',
                $this->escapeMdCell($milestone->display_date_label),
                $this->escapeMdCell($milestone->title),
                $status,
                $this->escapeMdCell($milestone->source_name),
                $this->escapeMdCell($milestone->description)
            );
        }
        $sections[] = implode("\n", $milestoneLines);

        // Editions Pricing Table
        $editionLines = [
            '## 2. Projected Editions & Regional Pricing Matrix',
            '',
            '| Edition | USD | GBP | EUR | CAD | AUD | JPY | Est. Size | Status |',
            '| --- | --- | --- | --- | --- | --- | --- | --- | --- |',
        ];
        foreach ($editions as $edition) {
            $editionLines[] = sprintf(
                '| %s | $%s | £%s | €%s | CA$%s | A$%s | ¥%s | %d GB | %s |',
                $this->escapeMdCell($edition->name),
                number_format($edition->price_usd, 2),
                number_format($edition->price_gbp, 2),
                number_format($edition->price_eur, 2),
                number_format($edition->price_cad, 2),
                number_format($edition->price_aud, 2),
                number_format($edition->price_jpy),
                $edition->estimated_size_gb,
                $edition->is_price_confirmed ? 'Confirmed' : 'Projected'
            );
        }
        $sections[] = implode("\n", $editionLines);

        // Platform Specs Table
        $platformLines = [
            '## 3. Platform Hardware, Download Sizes & SSD Throughput Matrix',
            '',
            '| Platform | Family | Base Size | Total w/ Hi-Res | Target Output | Upscaler | Min SSD Speed | RAM / VRAM | Release Window |',
            '| --- | --- | --- | --- | --- | --- | --- | --- | --- |',
        ];
        foreach ($platforms as $platform) {
            $platformLines[] = sprintf(
                '| %s | %s | %d GB | %d GB | %s @ %s | %s | %d MB/s | %d GB / %d GB | %s |',
                $this->escapeMdCell($platform->platform_name),
                strtoupper($platform->platform_family),
                $platform->estimated_download_gb,
                $platform->totalSizeWithHighResGb(),
                $this->escapeMdCell($platform->target_resolution),
                $this->escapeMdCell($platform->target_fps),
                $this->escapeMdCell($platform->upscaling_tech),
                $platform->min_ssd_speed_mbps,
                $platform->ram_gb,
                $platform->vram_gb,
                $this->escapeMdCell($platform->release_window)
            );
        }
        $sections[] = implode("\n", $platformLines);

        // Leonida Map Atlas Table
        $mapLines = [
            '## 4. State of Leonida Map Atlas & Regions',
            '',
            '| Region | Code | Biome | Est. Area (km²) | Real-World Inspiration | Status | Key Landmarks |',
            '| --- | --- | --- | --- | --- | --- | --- |',
        ];
        foreach ($locations as $location) {
            $mapLines[] = sprintf(
                '| %s | %s | %s | %.1f | %s | %s | %s |',
                $this->escapeMdCell($location->name),
                $this->escapeMdCell($location->region_code),
                $this->escapeMdCell($location->biome_type),
                $location->estimated_area_sq_km,
                $this->escapeMdCell($location->real_world_inspiration),
                $this->escapeMdCell(str_replace('_', ' ', $location->verification_status)),
                $this->escapeMdCell(implode(', ', $location->key_landmarks ?? []))
            );
        }
        $sections[] = implode("\n", $mapLines);

        // Full Articles
        $sections[] = '## 5. Complete Technical Articles';
        foreach ($posts as $post) {
            $published = ($post->published_at ?? $post->created_at)?->toIso8601String() ?? '';
            $updated = ($post->updated_at ?? $post->published_at ?? $post->created_at)?->toIso8601String() ?? '';
            $body = $blocks->renderMarkdown($post->content_blocks ?? []);

            $sections[] = implode("\n", [
                "### {$post->title}",
                "- Canonical URL: {$siteUrl}/gta/{$post->slug}",
                "- Markdown Mirror: {$siteUrl}/gta/{$post->slug}.md",
                "- Category: {$post->category->name}",
                "- Classification: {$post->verificationLabel()}",
                "- Author: {$post->author_name} ({$post->author_role})",
                "- Published: {$published} | Updated: {$updated}",
                '',
                $body,
            ]);
        }

        return response(implode("\n\n---\n\n", $sections)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }

    public function rssFeed(): Response
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');
        $posts = Post::query()
            ->published()
            ->with('category')
            ->orderByDesc('published_at')
            ->limit(25)
            ->get();

        $esc = static fn (string $value): string => htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
        $lastBuildDate = ($posts->first()?->updated_at ?? $posts->first()?->published_at ?? now())->toRfc2822String();

        $itemsXml = [];
        foreach ($posts as $post) {
            $link = "{$siteUrl}/gta/{$post->slug}";
            $pubDate = ($post->published_at ?? $post->created_at ?? now())->toRfc2822String();
            $itemsXml[] = implode("\n", [
                '    <item>',
                '      <title>'.$esc($post->title).'</title>',
                '      <link>'.$esc($link).'</link>',
                '      <guid isPermaLink="true">'.$esc($link).'</guid>',
                '      <pubDate>'.$esc($pubDate).'</pubDate>',
                '      <category>'.$esc($post->category?->name ?? 'GTA VI').'</category>',
                '      <description>'.$esc($post->excerpt).'</description>',
                '    </item>',
            ]);
        }

        $xml = implode("\n", [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">',
            '  <channel>',
            '    <title>GTAVI.PRO — Independent GTA VI Telemetry &amp; Dispatches</title>',
            '    <link>'.$esc($siteUrl.'/').'</link>',
            '    <description>Independent GTA VI release tracker, hardware telemetry, download size modeling, and Leonida analysis. Not affiliated with Rockstar Games or Take-Two Interactive.</description>',
            '    <language>en-us</language>',
            '    <lastBuildDate>'.$esc($lastBuildDate).'</lastBuildDate>',
            '    <atom:link href="'.$esc($siteUrl.'/feed.xml').'" rel="self" type="application/rss+xml" />',
            implode("\n", $itemsXml),
            '  </channel>',
            '</rss>',
            '',
        ]);

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }

    public function openApiJson(): JsonResponse
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');

        return response()->json([
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'GTAVI.PRO Public Telemetry & Editorial API',
                'version' => '1.0.0',
                'description' => 'Read-only JSON telemetry API for independent GTA VI release milestones, projected editions, hardware specs, Leonida map data, and download estimation. Not affiliated with Rockstar Games or Take-Two Interactive.',
            ],
            'servers' => [
                ['url' => $siteUrl, 'description' => 'Production GTAVI.PRO Server'],
            ],
            'paths' => [
                '/api/v1/posts' => [
                    'get' => [
                        'operationId' => 'listPublishedPosts',
                        'summary' => 'List published technical dispatches',
                        'responses' => ['200' => ['description' => 'Paginated list of published articles']],
                    ],
                ],
                '/api/v1/posts/{slug}' => [
                    'get' => [
                        'operationId' => 'getPublishedPost',
                        'summary' => 'Get a single published technical dispatch by slug',
                        'parameters' => [
                            ['name' => 'slug', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'responses' => ['200' => ['description' => 'Single article payload']],
                    ],
                ],
                '/api/v1/editions' => [
                    'get' => [
                        'operationId' => 'listEditions',
                        'summary' => 'List projected GTA VI editions and multi-currency pricing',
                        'responses' => ['200' => ['description' => 'Edition pricing matrix']],
                    ],
                ],
                '/api/v1/platforms' => [
                    'get' => [
                        'operationId' => 'listPlatforms',
                        'summary' => 'List projected platform specs and download footprints',
                        'responses' => ['200' => ['description' => 'Platform hardware profiles']],
                    ],
                ],
                '/api/v1/milestones' => [
                    'get' => [
                        'operationId' => 'listMilestones',
                        'summary' => 'List chronological Rockstar Games and Take-Two Interactive milestones',
                        'responses' => ['200' => ['description' => 'Milestone timeline']],
                    ],
                ],
                '/api/v1/locations' => [
                    'get' => [
                        'operationId' => 'listMapLocations',
                        'summary' => 'List State of Leonida regions, biomes, and landmarks',
                        'responses' => ['200' => ['description' => 'Leonida cartography profiles']],
                    ],
                ],
                '/calculators/download-estimate' => [
                    'post' => [
                        'operationId' => 'calculateDownloadEstimate',
                        'summary' => 'Calculate GTA VI download time and safe NVMe SSD headroom',
                        'responses' => ['200' => ['description' => 'Calculated transfer estimate']],
                    ],
                ],
            ],
        ], 200, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function ogImageSvg(string $slug): Response
    {
        $hubCards = [
            'default' => ['GTAVI.PRO — GTA VI Release Info, Calculators & Telemetry', 'INDEPENDENT TELEMETRY DESK', 'Verified Filings • Hardware Specs • Leonida Atlas'],
            'release-info' => ['GTA VI Release Info & Verified Filings Timeline', 'OFFICIAL TIMELINE LEDGER', 'Nov 19, 2026 Console Target • Take-Two IR Log'],
            'prices-editions' => ['GTA VI Prices & Editions Multi-Currency Matrix', 'RETAIL PROJECTIONS', 'Standard, Deluxe & Collector Tiers • USD/GBP/EUR/CAD/AUD/JPY'],
            'download-sizes' => ['GTA VI Download Sizes & NVMe SSD Requirements', 'STORAGE & HARDWARE', 'PS5, PS5 Pro PSSR, Xbox Series X|S & PC Tiers'],
            'calculators' => ['Interactive GTA VI Telemetry Calculators', 'LIVE TOOLKIT', 'Download Time • PC FPS Readiness • Edition Value • Map Scale'],
            'map-atlas' => ['State of Leonida Map Atlas & 6 Confirmed Biomes', 'CARTOGRAPHY TELEMETRY', 'Vice City • Keys • Grassrivers • Port Gellhorn • Ambrosia • Kalaga'],
            'blog' => ['Vice Telemetry Blog — Deep Technical Dispatches', 'EDITORIAL NEWSROOM', 'Hardware Benchmarks • Filings • Pricing • Cartography'],
            'disclaimer' => ['GTAVI.PRO Legal Non-Affiliation & Verification Policy', 'EDITORIAL STANDARDS', 'Independent Publication • Zero Rockstar / Take-Two Affiliation'],
        ];

        $headline = $hubCards[$slug][0] ?? null;
        $kicker = $hubCards[$slug][1] ?? 'GTAVI.PRO DISPATCH';
        $subline = $hubCards[$slug][2] ?? 'Independent Technical Analysis & Telemetry';

        if ($headline === null) {
            $post = Post::query()->published()->with('category')->where('slug', $slug)->first();
            abort_if($post === null, 404);

            $headline = $post->title;
            $kicker = strtoupper($post->category?->name ?? 'GTA VI REPORT').' • '.strtoupper($post->verificationLabel());
            $subline = sprintf('%d words • %d min read • Clean Markdown Twin Available', $post->word_count, $post->reading_time_minutes);
        }

        $wrappedLines = array_slice(explode("\n", wordwrap($headline, 34, "\n", true)), 0, 3);
        $esc = static fn (string $value): string => htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');

        $titleSvg = '';
        foreach ($wrappedLines as $index => $line) {
            $y = 265 + ($index * 68);
            $titleSvg .= sprintf(
                '<text x="72" y="%d" font-family="system-ui, -apple-system, sans-serif" font-size="52" font-weight="800" fill="#ffffff">%s</text>'."\n",
                $y,
                $esc($line)
            );
        }

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630" role="img" aria-label="{$esc($headline)}">
  <rect width="1200" height="630" fill="#180b2b"/>
  <rect x="48" y="48" width="1104" height="534" rx="16" fill="#24103f" stroke="#4c287c" stroke-width="2"/>
  <rect x="48" y="48" width="1104" height="8" rx="4" fill="#be185d"/>
  <rect x="72" y="96" width="132" height="44" rx="6" fill="#331858" stroke="#be185d" stroke-width="2"/>
  <text x="138" y="125" text-anchor="middle" font-family="system-ui, -apple-system, sans-serif" font-size="22" font-weight="800" fill="#ffffff">GTA VI</text>
  <text x="220" y="126" font-family="system-ui, -apple-system, sans-serif" font-size="24" font-weight="800" fill="#f9a8d4">.PRO</text>
  <text x="72" y="192" font-family="ui-monospace, monospace" font-size="18" font-weight="700" fill="#f472b6" letter-spacing="1.5">{$esc(Str::limit($kicker, 64))}</text>
  {$titleSvg}
  <text x="72" y="492" font-family="system-ui, -apple-system, sans-serif" font-size="22" font-weight="500" fill="#ddd6fe">{$esc(Str::limit($subline, 86))}</text>
  <line x1="72" y1="524" x2="1128" y2="524" stroke="#3b2063" stroke-width="2"/>
  <text x="72" y="556" font-family="system-ui, -apple-system, sans-serif" font-size="16" font-weight="600" fill="#c4b5fd">GTAVI.PRO — Independent Telemetry Desk • Not affiliated with Rockstar Games or Take-Two Interactive</text>
</svg>
SVG;

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function escapeMdCell(string $value): string
    {
        return str_replace(['|', "\r", "\n"], ['\|', ' ', ' '], trim($value));
    }
}
