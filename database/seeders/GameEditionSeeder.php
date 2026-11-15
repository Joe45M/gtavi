<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GameEdition;
use Illuminate\Database\Seeder;

final class GameEditionSeeder extends Seeder
{
    public function run(): void
    {
        $editions = [
            [
                'name' => 'Standard Edition',
                'slug' => 'standard-edition',
                'tagline' => 'Full State of Leonida story campaign + day-one access to the next-generation online framework.',
                'price_usd' => 69.99,
                'price_gbp' => 69.99,
                'price_eur' => 79.99,
                'price_cad' => 89.99,
                'price_aud' => 109.95,
                'price_jpy' => 9800,
                'is_price_confirmed' => false,
                'estimated_size_gb' => 155,
                'early_access_hours' => 0,
                'online_bonus_credits' => 250000,
                'physical_collectibles' => [
                    'Physical disc / case insert (retail SKUs)',
                    'Fold-out State of Leonida district street map (traditional retail projection)',
                ],
                'digital_bonuses' => [
                    'Full Story Campaign (Lucia & Jason)',
                    'Access to next-gen online ecosystem at launch',
                    'Projected pre-order vehicle livery & starter cash',
                ],
                'badge_text' => 'Baseline Projection',
                'is_highlighted' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Vice City Deluxe Edition',
                'slug' => 'vice-city-deluxe-edition',
                'tagline' => 'Expanded digital launch tier with Ocean Beach vehicle pack, bonus online bankroll, and soundtrack stems.',
                'price_usd' => 99.99,
                'price_gbp' => 89.99,
                'price_eur' => 99.99,
                'price_cad' => 129.99,
                'price_aud' => 149.95,
                'price_jpy' => 13800,
                'is_price_confirmed' => false,
                'estimated_size_gb' => 165,
                'early_access_hours' => 48,
                'online_bonus_credits' => 1500000,
                'physical_collectibles' => [
                    'Digital-first SKU (optional retail SteelBook variant)',
                ],
                'digital_bonuses' => [
                    'Full Story Campaign + Digital Artbook Archive',
                    'Projected 48-Hour Campaign Pre-Launch Window',
                    '1,500,000 Online Launch Credits + Vice Customs Pack',
                    'High-Resolution Texture & Audio Mastering Pack (+10 GB)',
                ],
                'badge_text' => 'Most Popular Tier',
                'is_highlighted' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Leonida Collector\'s Crate',
                'slug' => 'leonida-collectors-crate',
                'tagline' => 'Physical collector showcase projection built around Vice City memorabilia and multi-format steel packaging.',
                'price_usd' => 229.99,
                'price_gbp' => 199.99,
                'price_eur' => 229.99,
                'price_cad' => 299.99,
                'price_aud' => 349.95,
                'price_jpy' => 33000,
                'is_price_confirmed' => false,
                'estimated_size_gb' => 170,
                'early_access_hours' => 72,
                'online_bonus_credits' => 3500000,
                'physical_collectibles' => [
                    'Vice Neon Embossed SteelBook Case',
                    'Hardcover 160-Page Art & Architecture of Leonida Book',
                    'Die-Cast Vice City Police Interceptor Scale Model',
                    'Waterproof Cotton-Rag Foldout Leonida Highway Map',
                    'Numbered Metal Leonida License Plate Replica',
                ],
                'digital_bonuses' => [
                    'All Deluxe Edition Digital Entitlements',
                    'Projected 72-Hour Early Access Key',
                    '3,500,000 Online Launch Credits + Exclusive Penthouse Loft',
                ],
                'badge_text' => 'Limited Projection',
                'is_highlighted' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($editions as $edition) {
            GameEdition::query()->updateOrCreate(
                ['slug' => $edition['slug']],
                $edition
            );
        }
    }
}
