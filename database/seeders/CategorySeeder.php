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
                'description' => 'Chronological tracking of official Rockstar Games announcements, trailer benchmarks, and Take-Two Interactive investor guidance.',
                'accent_color' => '#ff1e7a',
                'meta_title' => 'GTA VI Release Date & Take-Two Filings Tracker | GTAVI.PRO',
                'meta_description' => 'Track verified Rockstar Games announcements, Take-Two Interactive earnings call updates, and console-to-PC launch timeline analysis.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Hardware, Specs & Download Math',
                'slug' => 'hardware-specs-download-math',
                'description' => 'Storage footprint projections, NVMe streaming throughput, ray-tracing budgets, and PC readiness modeling for RAGE 9.',
                'accent_color' => '#00f0ff',
                'meta_title' => 'GTA VI Download Sizes, SSD & Hardware Specs | GTAVI.PRO',
                'meta_description' => 'Independent hardware telemetry covering projected GTA VI download sizes, PS5 Pro PSSR upscaling, Xbox Series X/S targets, and PC VRAM budgets.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Vice City & Leonida Atlas',
                'slug' => 'vice-city-leonida-atlas',
                'description' => 'Geographic scale comparisons, district breakdowns, biomes, and transit estimates across the State of Leonida.',
                'accent_color' => '#c026d3',
                'meta_title' => 'Leonida Map Atlas & Vice City Scale Analysis | GTAVI.PRO',
                'meta_description' => 'Explore the projected biomes, counties, landmarks, and square-kilometer scale of the State of Leonida and modern Vice City.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Editions, Pricing & Pre-Orders',
                'slug' => 'editions-pricing-pre-orders',
                'description' => 'Regional currency conversions, AAA pricing tier economics, digital bonus comparisons, and collector edition forecasts.',
                'accent_color' => '#ff6bd6',
                'meta_title' => 'GTA VI Price Tiers & Editions Breakdown | GTAVI.PRO',
                'meta_description' => 'Compare projected GTA VI Standard, Deluxe, and Collector editions across USD, GBP, EUR, CAD, AUD, and JPY with cost-per-hour math.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Trailer & RAGE 9 Engine Analysis',
                'slug' => 'trailer-rage-9-analysis',
                'description' => 'Frame-by-frame rendering analysis covering global illumination, water simulation, crowd density, and shader pipelines.',
                'accent_color' => '#8b5cf6',
                'meta_title' => 'RAGE 9 Engine & Trailer Technical Analysis | GTAVI.PRO',
                'meta_description' => 'Deep technical dispatches dissecting RAGE 9 ray tracing, volumetric atmosphere, NPC scheduling, and frame-time targets.',
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
