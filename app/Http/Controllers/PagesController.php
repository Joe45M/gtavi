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
                ogImage: $siteUrl.'/og/default.svg',
            ),
        ]);
    }

    public function releaseInfo(MicrositeDataService $data, SeoService $seo): View
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');
        $milestones = $data->milestones();
        $platforms = $data->platformSpecs();

        $itemList = [];
        foreach ($milestones->values() as $idx => $milestone) {
            $itemList[] = [
                '@type' => 'ListItem',
                'position' => $idx + 1,
                'name' => $milestone->display_date_label.' — '.$milestone->title,
                'description' => $milestone->description,
            ];
        }

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
                ogImage: $siteUrl.'/og/release-info.svg',
                extraGraphNodes: [
                    [
                        '@type' => 'Dataset',
                        '@id' => $siteUrl.'/release-info#dataset',
                        'name' => 'GTA VI Official Announcements & Release Milestones Dataset',
                        'description' => 'Verified chronological dataset of Rockstar Games Newswire disclosures, Take-Two Interactive earnings calls, and projected PC port timelines.',
                        'url' => $siteUrl.'/release-info',
                        'isAccessibleForFree' => true,
                        'creator' => ['@id' => $siteUrl.'/#organization'],
                        'distribution' => [
                            '@type' => 'DataDownload',
                            'encodingFormat' => 'application/json',
                            'contentUrl' => $siteUrl.'/api/v1/milestones',
                        ],
                    ],
                    [
                        '@type' => 'ItemList',
                        'name' => 'GTA VI Announcement & Launch Timeline',
                        'itemListElement' => $itemList,
                    ],
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
                ogImage: $siteUrl.'/og/prices-editions.svg',
                extraGraphNodes: [
                    [
                        '@type' => 'Dataset',
                        '@id' => $siteUrl.'/prices-editions#dataset',
                        'name' => 'GTA VI Multi-Currency Edition Pricing Projection Dataset',
                        'description' => 'Modeled retail pricing matrix across USD, GBP, EUR, CAD, AUD, and JPY for Standard, Deluxe, and Collector editions.',
                        'url' => $siteUrl.'/prices-editions',
                        'isAccessibleForFree' => true,
                        'creator' => ['@id' => $siteUrl.'/#organization'],
                        'distribution' => [
                            '@type' => 'DataDownload',
                            'encodingFormat' => 'application/json',
                            'contentUrl' => $siteUrl.'/api/v1/editions',
                        ],
                    ],
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
                ogImage: $siteUrl.'/og/download-sizes.svg',
                extraGraphNodes: [
                    [
                        '@type' => 'Dataset',
                        '@id' => $siteUrl.'/download-sizes#dataset',
                        'name' => 'GTA VI Platform Storage, SSD Speed & Hardware Specs Dataset',
                        'description' => 'Projected base and hi-res pack download sizes, SSD throughput minimums, RAM/VRAM budgets, and upscaling targets across PS5, PS5 Pro, Xbox Series X|S, and PC.',
                        'url' => $siteUrl.'/download-sizes',
                        'isAccessibleForFree' => true,
                        'creator' => ['@id' => $siteUrl.'/#organization'],
                        'distribution' => [
                            '@type' => 'DataDownload',
                            'encodingFormat' => 'application/json',
                            'contentUrl' => $siteUrl.'/api/v1/platforms',
                        ],
                    ],
                ],
            ),
        ]);
    }

    public function calculators(Request $request, SeoService $seo): View
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');
        $rawTab = (string) $request->query('tab', '');
        $validTabs = ['download', 'pc', 'edition', 'map'];
        $hasSpecificTab = in_array($rawTab, $validTabs, true);
        $tab = $hasSpecificTab ? $rawTab : 'download';

        $tabMeta = [
            'download' => [
                'title' => 'GTA VI Download Time & Pre-Load Bandwidth Calculator | GTAVI.PRO',
                'description' => 'Calculate exact GTA VI download duration across Mbps, MB/s, or Gbps connections with CDN overhead, data cap usage, and safe NVMe SSD headroom.',
            ],
            'pc' => [
                'title' => 'GTA VI PC Requirements, VRAM & FPS Readiness Calculator | GTAVI.PRO',
                'description' => 'Test your desktop CPU, GPU VRAM, system RAM, ray tracing, and DLSS/FSR upscaling against projected GTA VI RAGE 9 PC performance targets.',
            ],
            'edition' => [
                'title' => 'GTA VI Edition Value & Multi-Currency Cost-Per-Hour Calculator | GTAVI.PRO',
                'description' => 'Compare Standard, Deluxe, and Collector GTA VI edition projections across USD, GBP, EUR, CAD, AUD, and JPY against expected play hours.',
            ],
            'map' => [
                'title' => 'State of Leonida Map Scale & Vehicle Travel Time Calculator | GTAVI.PRO',
                'description' => 'Compare projected 125 km² Leonida scale against GTA V Los Santos and calculate cross-state driving, airboat, and jet traversal times.',
            ],
        ];

        $pageTitle = $hasSpecificTab
            ? $tabMeta[$tab]['title']
            : 'GTA VI Calculators: Download Time, PC FPS Readiness & Edition Value | GTAVI.PRO';
        $pageDescription = $hasSpecificTab
            ? $tabMeta[$tab]['description']
            : 'Interactive GTA VI calculators: compute pre-load download time, test PC VRAM and CPU bottlenecks, compare edition currencies, and estimate Leonida travel times.';
        $canonicalUrl = $hasSpecificTab ? $siteUrl.'/calculators?tab='.$tab : $siteUrl.'/calculators';

        return view('pages.calculators', [
            'initialTab' => $tab,
            'seoMeta' => $seo->buildPageMeta(
                title: $pageTitle,
                description: $pageDescription,
                canonicalUrl: $canonicalUrl,
                breadcrumbs: [
                    ['name' => 'Home', 'url' => $siteUrl.'/'],
                    ['name' => 'Calculators', 'url' => $siteUrl.'/calculators'],
                ],
                ogImage: $siteUrl.'/og/calculators.svg',
                extraGraphNodes: [
                    [
                        '@type' => 'SoftwareApplication',
                        '@id' => $canonicalUrl.'#software',
                        'name' => 'GTAVI.PRO Interactive Telemetry Calculator Suite',
                        'applicationCategory' => 'UtilitiesApplication',
                        'operatingSystem' => 'Any',
                        'url' => $canonicalUrl,
                        'offers' => [
                            '@type' => 'Offer',
                            'price' => '0',
                            'priceCurrency' => 'USD',
                        ],
                        'featureList' => [
                            'Pre-load download time and ISP data cap estimator',
                            'PC CPU, GPU, VRAM, and DLSS/FSR bottleneck analyzer',
                            'Multi-currency edition cost-per-hour calculator',
                            'State of Leonida map scale and traversal estimator',
                        ],
                    ],
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
                description: 'Explore Vice City Metro, Leonida Keys, Grassrivers, Port Gellhorn, and Ambrosia with projected square-kilometer metrics and landmark lists.',
                canonicalUrl: $siteUrl.'/map-atlas',
                breadcrumbs: [
                    ['name' => 'Home', 'url' => $siteUrl.'/'],
                    ['name' => 'Leonida Map Atlas', 'url' => $siteUrl.'/map-atlas'],
                ],
                ogImage: $siteUrl.'/og/map-atlas.svg',
                extraGraphNodes: [
                    [
                        '@type' => 'Dataset',
                        '@id' => $siteUrl.'/map-atlas#dataset',
                        'name' => 'State of Leonida Cartography & Biome Projections Dataset',
                        'description' => 'Telemetry dataset detailing the six confirmed State of Leonida regions, biome profiles, landmark lists, and projected area metrics.',
                        'url' => $siteUrl.'/map-atlas',
                        'isAccessibleForFree' => true,
                        'creator' => ['@id' => $siteUrl.'/#organization'],
                        'distribution' => [
                            '@type' => 'DataDownload',
                            'encodingFormat' => 'application/json',
                            'contentUrl' => $siteUrl.'/api/v1/locations',
                        ],
                    ],
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
                ogImage: $siteUrl.'/og/disclaimer.svg',
            ),
        ]);
    }
}
