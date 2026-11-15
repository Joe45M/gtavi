<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MicrositeDataService;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final readonly class PagesController
{
    public function home(MicrositeDataService $data, SeoService $seo): View
    {
        $summary = $data->homeSummary();
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');

        return view('pages.home', [
            'summary' => $summary,
            'seoMeta' => $seo->buildPageMeta(
                title: 'GTAVI.PRO — GTA VI Release Info, Calculators, Download Sizes & Blog',
                description: 'Independent GTA VI microsite & technical blog for gtavi.pro: release timeline, edition pricing, 155-185 GB download calculators, Leonida map atlas. Not affiliated with Rockstar Games or Take-Two Interactive.',
                canonicalUrl: $siteUrl.'/',
                breadcrumbs: [
                    ['name' => 'Home', 'url' => $siteUrl.'/'],
                ],
                faqItems: [
                    [
                        'question' => 'Is GTAVI.PRO affiliated with Rockstar Games or Take-Two Interactive?',
                        'answer' => 'No. GTAVI.PRO is an independent fan and technical telemetry publication with zero affiliation, sponsorship, or endorsement from Rockstar Games or Take-Two Interactive Software, Inc.',
                    ],
                    [
                        'question' => 'What tools does GTAVI.PRO include?',
                        'answer' => 'GTAVI.PRO provides interactive calculators for download time, PC hardware VRAM/FPS readiness, edition regional pricing, and Leonida map traversal, plus a full block-based editorial CMS studio.',
                    ],
                ],
            ),
        ]);
    }

    public function releaseInfo(MicrositeDataService $data, SeoService $seo): View
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');
        $milestones = $data->milestones();
        $platforms = $data->platformSpecs();

        return view('pages.release-info', [
            'milestones' => $milestones,
            'platforms' => $platforms,
            'seoMeta' => $seo->buildPageMeta(
                title: 'GTA VI Release Info, Official Timeline & Console Windows | GTAVI.PRO',
                description: 'Verified chronological tracker of Rockstar Games announcements, Take-Two Interactive filings, and projected PC release cadence. Unofficial independent resource.',
                canonicalUrl: $siteUrl.'/release-info',
                breadcrumbs: [
                    ['name' => 'Home', 'url' => $siteUrl.'/'],
                    ['name' => 'Release Info', 'url' => $siteUrl.'/release-info'],
                ],
            ),
        ]);
    }

    public function pricesEditions(MicrositeDataService $data, SeoService $seo): View
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');
        $editions = $data->editions();

        return view('pages.prices-editions', [
            'editions' => $editions,
            'seoMeta' => $seo->buildPageMeta(
                title: 'GTA VI Prices & Editions Matrix (USD, GBP, EUR, CAD, AUD, JPY) | GTAVI.PRO',
                description: 'Compare projected GTA VI Standard, Vice City Deluxe, and Collector editions across global currencies, digital bonuses, and storage footprints. Independent estimate.',
                canonicalUrl: $siteUrl.'/prices-editions',
                breadcrumbs: [
                    ['name' => 'Home', 'url' => $siteUrl.'/'],
                    ['name' => 'Prices & Editions', 'url' => $siteUrl.'/prices-editions'],
                ],
            ),
        ]);
    }

    public function downloadSizes(MicrositeDataService $data, SeoService $seo): View
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');
        $platforms = $data->platformSpecs();

        return view('pages.download-sizes', [
            'platforms' => $platforms,
            'seoMeta' => $seo->buildPageMeta(
                title: 'GTA VI Download Sizes, SSD Throughput & Platform Specs | GTAVI.PRO',
                description: 'Projected install footprints (118–225 GB), PS5 Pro PSSR modes, Xbox Series X|S targets, and PC NVMe SSD requirements for GTA VI. Unofficial analysis.',
                canonicalUrl: $siteUrl.'/download-sizes',
                breadcrumbs: [
                    ['name' => 'Home', 'url' => $siteUrl.'/'],
                    ['name' => 'Download Sizes & Specs', 'url' => $siteUrl.'/download-sizes'],
                ],
            ),
        ]);
    }

    public function calculators(Request $request, SeoService $seo): View
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');
        $tab = (string) $request->query('tab', 'download');
        if (! in_array($tab, ['download', 'pc', 'edition', 'map'], true)) {
            $tab = 'download';
        }

        return view('pages.calculators', [
            'initialTab' => $tab,
            'seoMeta' => $seo->buildPageMeta(
                title: 'GTA VI Calculators: Download Time, PC FPS Readiness & Edition Value | GTAVI.PRO',
                description: 'Interactive GTA VI calculators: compute pre-load download time, test PC VRAM and CPU bottlenecks, compare edition currencies, and estimate Leonida travel times.',
                canonicalUrl: $siteUrl.'/calculators',
                breadcrumbs: [
                    ['name' => 'Home', 'url' => $siteUrl.'/'],
                    ['name' => 'Calculators', 'url' => $siteUrl.'/calculators'],
                ],
            ),
        ]);
    }

    public function mapAtlas(MicrositeDataService $data, SeoService $seo): View
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');
        $locations = $data->mapLocations();

        return view('pages.map-atlas', [
            'locations' => $locations,
            'totalArea' => (float) $locations->sum('estimated_area_sq_km'),
            'seoMeta' => $seo->buildPageMeta(
                title: 'State of Leonida Map Atlas & Vice City Region Telemetry | GTAVI.PRO',
                description: 'Explore Vice City Metro, Leonida Keys, Grassrivers wetlands, Port Gellhorn, and Ambrosia with projected square-kilometer metrics and landmark lists.',
                canonicalUrl: $siteUrl.'/map-atlas',
                breadcrumbs: [
                    ['name' => 'Home', 'url' => $siteUrl.'/'],
                    ['name' => 'Leonida Map Atlas', 'url' => $siteUrl.'/map-atlas'],
                ],
            ),
        ]);
    }

    public function disclaimer(SeoService $seo): View
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');

        return view('pages.disclaimer', [
            'seoMeta' => $seo->buildPageMeta(
                title: 'Legal Disclaimer & Non-Affiliation Statement | GTAVI.PRO',
                description: 'GTAVI.PRO is an independent fan and technical analysis publication not affiliated with, endorsed by, or connected to Rockstar Games or Take-Two Interactive.',
                canonicalUrl: $siteUrl.'/disclaimer',
                breadcrumbs: [
                    ['name' => 'Home', 'url' => $siteUrl.'/'],
                    ['name' => 'Disclaimer', 'url' => $siteUrl.'/disclaimer'],
                ],
            ),
        ]);
    }
}
