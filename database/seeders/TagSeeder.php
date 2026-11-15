<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

final class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'Download Size', 'slug' => 'download-size'],
            ['name' => 'Pre-Load Guide', 'slug' => 'pre-load-guide'],
            ['name' => 'PS5 Pro PSSR', 'slug' => 'ps5-pro-pssr'],
            ['name' => 'PC Requirements', 'slug' => 'pc-requirements'],
            ['name' => 'RAGE 9 Engine', 'slug' => 'rage-9-engine'],
            ['name' => 'Leonida Map', 'slug' => 'leonida-map'],
            ['name' => 'Vice City', 'slug' => 'vice-city'],
            ['name' => 'Pricing & Editions', 'slug' => 'pricing-editions'],
            ['name' => 'Take-Two Earnings', 'slug' => 'take-two-earnings'],
            ['name' => 'Ray Tracing', 'slug' => 'ray-tracing'],
            ['name' => 'SSD Throughput', 'slug' => 'ssd-throughput'],
            ['name' => 'Release Timeline', 'slug' => 'release-timeline'],
        ];

        foreach ($tags as $tag) {
            Tag::query()->updateOrCreate(
                ['slug' => $tag['slug']],
                $tag
            );
        }
    }
}
