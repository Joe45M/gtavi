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
                'tagline' => 'Full State of Leonida story campaign starring Jason Duval and Lucia Caminos, plus day-one access to the online ecosystem.',
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
                    'Physical disc / case insert (retail SKUs, unconfirmed)',
                    'Fold-out State of Leonida street map (traditional Rockstar retail estimate)',
                ],
                'digital_bonuses' => [
                    'Full Story Campaign (Jason Duval & Lucia Caminos)',
                    'Access to next-gen online ecosystem when live',
                    'Projected pre-order starter vehicle & cash bonus',
                ],
                'badge_text' => 'Baseline Estimate',
                'is_highlighted' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Vice City Deluxe Edition',
                'slug' => 'vice-city-deluxe-edition',
                'tagline' => 'Projected digital mid-tier edition modeled after GTA V and RDR2 Special/Ultimate editions, with bonus vehicles and online bankroll.',
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
                    'Digital-first SKU (possible retail SteelBook variant)',
                ],
                'digital_bonuses' => [
                    'Full Story Campaign + Digital Art Archive (Estimated)',
                    'Projected 48-Hour Campaign Access Window (Unconfirmed)',
                    '1,500,000 Online Launch Credits + Custom Vehicle Pack',
                    'Optional High-Resolution Audio & Texture Assets (+10 GB est.)',
                ],
                'badge_text' => 'Projected Mid-Tier',
                'is_highlighted' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Leonida Collector\'s Crate',
                'slug' => 'leonida-collectors-crate',
                'tagline' => 'Physical collector edition estimate modeled on GTA V and RDR2 Collector boxes with SteelBook, art book, and physical map.',
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
                    'Vice Neon Embossed SteelBook Case (Estimated)',
                    'Hardcover Art & Architecture of Leonida Book (Estimated)',
                    'Die-Cast Vice City Police Cruiser Scale Model (Estimated)',
                    'Printed Cotton-Rag Leonida Highway Map (Estimated)',
                    'Numbered Metal Leonida License Plate Replica (Estimated)',
                ],
                'digital_bonuses' => [
                    'All Deluxe Edition Digital Entitlements (Estimated)',
                    'Projected 72-Hour Early Access Key (Unconfirmed)',
                    '3,500,000 Online Launch Credits + Safehouse Perk',
                ],
                'badge_text' => 'Collector Estimate',
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
