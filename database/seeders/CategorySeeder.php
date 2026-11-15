<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

final class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Release & Take-Two Filings',
                'slug' => 'release-take-two-filings',
                'description' => 'Chronological tracking of official Rockstar Games announcements, the November 19, 2026 console launch date, and Take-Two Interactive investor disclosures.',
                'accent_color' => '#ff1e7a',
                'meta_title' => 'GTA VI Release Date & Take-Two Filings Tracker | GTAVI.PRO',
                'meta_description' => 'Track verified Rockstar Games Newswire announcements, the November 19, 2026 console launch date, and Take-Two investor updates.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Hardware, Specs & Download Math',
                'slug' => 'hardware-specs-download-math',
                'description' => 'Storage footprint estimates, NVMe streaming throughput, ray-tracing memory budgets, and PC readiness modeling for PS5, Xbox Series X|S, and future PC builds.',
                'accent_color' => '#00f0ff',
                'meta_title' => 'GTA VI Download Sizes, SSD & Hardware Specs | GTAVI.PRO',
                'meta_description' => 'Independent hardware guides covering projected GTA VI download sizes, PS5 Pro PSSR upscaling, Xbox Series X|S, and PC specs.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Vice City & Leonida Atlas',
                'slug' => 'vice-city-leonida-atlas',
                'description' => 'Breakdowns of the six officially revealed Leonida regions—Vice City, Leonida Keys, Grassrivers, Port Gellhorn, Ambrosia, and Mount Kalaga—alongside scale estimates.',
                'accent_color' => '#c026d3',
                'meta_title' => 'Leonida Map Atlas & Vice City Scale Analysis | GTAVI.PRO',
                'meta_description' => 'Explore the confirmed regions of Leonida, including Vice City, Leonida Keys, Grassrivers, Port Gellhorn, Ambrosia, and Mount Kalaga.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Editions, Pricing & Pre-Orders',
                'slug' => 'editions-pricing-pre-orders',
                'description' => 'Regional currency parity, AAA retail tier economics, digital bonus comparisons, and collector edition forecasts clearly separated from unannounced MSRPs.',
                'accent_color' => '#ff6bd6',
                'meta_title' => 'GTA VI Price Tiers & Editions Breakdown | GTAVI.PRO',
                'meta_description' => 'Compare projected GTA VI Standard, Deluxe, and Collector editions across USD, GBP, EUR, CAD, AUD, and JPY with cost-per-hour math.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Trailer & RAGE 9 Engine Analysis',
                'slug' => 'trailer-rage-9-analysis',
                'description' => 'Frame-by-frame technical analysis of Trailers 1 and 2, base PlayStation 5 capture footage, ray-traced global illumination, water physics, and character shaders.',
                'accent_color' => '#8b5cf6',
                'meta_title' => 'RAGE 9 Engine & Trailer Technical Analysis | GTAVI.PRO',
                'meta_description' => 'Technical breakdowns of Rockstar Trailers 1 and 2, base PS5 capture benchmarks, RAGE 9 ray tracing, water physics, and hair strands.',
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
