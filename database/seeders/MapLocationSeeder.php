<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MapLocation;
use Illuminate\Database\Seeder;

final class MapLocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Vice City Metro & Ocean Beach',
                'slug' => 'vice-city-metro-ocean-beach',
                'region_code' => 'VC-01',
                'real_world_inspiration' => 'Miami & South Beach, FL',
                'biome_type' => 'Neon Coastal Metropolis',
                'estimated_area_sq_km' => 34.50,
                'key_landmarks' => ['Ocean Beach Art Deco Strip', 'Ocean View Hotel', 'Little Cuba', 'Tisha-Wocka Flea Market', 'VC Port Cruise Terminals', 'Vice City International Airport'],
                'vehicle_types' => ['Hypercars', 'Lowriders', 'Superyachts', 'Police Cruisers', 'SeaPlanes'],
                'description' => 'The vibrant, sun-and-neon metropolitan core of Leonida. Spans pastel Ocean Beach Art Deco blocks, Little Cuba avenues, bustling VC Port cruise terminals, and high-rise financial towers.',
                'verification_status' => 'trailer_confirmed',
                'sort_order' => 1,
            ],
            [
                'name' => 'Leonida Keys',
                'slug' => 'leonida-keys',
                'region_code' => 'LK-02',
                'real_world_inspiration' => 'Florida Keys & Overseas Highway',
                'biome_type' => 'Tropical Archipelago & Coral Reef',
                'estimated_area_sq_km' => 19.20,
                'key_landmarks' => ['Overseas Highway Bridges', 'Brian Heder\'s Boat Yard & Marina', 'Coral Dive Reefs', 'Tiki Sandbars', 'Deep-Channel Smuggling Lanes'],
                'vehicle_types' => ['Speedboats', 'Jet Skis', 'Amphibious Planes', 'Convertibles'],
                'description' => 'A laid-back southern island chain linked by sweeping concrete highway spans, featuring clear turquoise shoals, bustling docks, and hidden smuggling coves around Brian Heder\'s operations.',
                'verification_status' => 'trailer_confirmed',
                'sort_order' => 2,
            ],
            [
                'name' => 'Grassrivers Wetlands',
                'slug' => 'grassrivers-wetlands',
                'region_code' => 'GR-03',
                'real_world_inspiration' => 'Florida Everglades & Big Cypress',
                'biome_type' => 'Subtropical Sawgrass Swamp',
                'estimated_area_sq_km' => 24.80,
                'key_landmarks' => ['Primordial Mangrove Swamps', 'Airboat Channels', 'Alligator Mudflats', 'Cypress Canopy', 'Off-Grid Camps'],
                'vehicle_types' => ['Fan Airboats', 'Lifted Mud Trucks', 'Hovercraft', 'Utility Helicopters'],
                'description' => 'Untamed mangrove wetlands south of the urban sprawl, highlighted in Rockstar\'s official showcase with dense sawgrass, murky water physics, alligators, and off-grid boat landings.',
                'verification_status' => 'trailer_confirmed',
                'sort_order' => 3,
            ],
            [
                'name' => 'Port Gellhorn',
                'slug' => 'port-gellhorn',
                'region_code' => 'PG-04',
                'real_world_inspiration' => 'Panama City / Fort Myers / Gulf Coast',
                'biome_type' => 'Gulf Industrial & Motel Strip',
                'estimated_area_sq_km' => 16.40,
                'key_landmarks' => ['Forgotten Gulf Motel Strip', 'Port Gellhorn Pawn & Loan', 'Uncle Jack\'s Liquor', 'Shut-Down Attractions', 'Industrial Port Docks'],
                'vehicle_types' => ['Muscle Cars', 'Motocross Dirt Bikes', 'Cargo Semi-Trucks', 'Sheriff SUVs'],
                'description' => 'A faded former vacation hub on Leonida\'s western coast defined by cheap roadside motels, pawn shops, strip malls, and hard-edged highway policing.',
                'verification_status' => 'trailer_confirmed',
                'sort_order' => 4,
            ],
            [
                'name' => 'Ambrosia & Lake Leonida Basin',
                'slug' => 'ambrosia-lake-leonida',
                'region_code' => 'AL-05',
                'real_world_inspiration' => 'Lake Okeechobee & Clewiston Sugar Belt',
                'biome_type' => 'Inland Agricultural & Industrial Lake',
                'estimated_area_sq_km' => 17.60,
                'key_landmarks' => ['Allied Crystal Sugar Refinery', 'Lake Leonida Levee Loop', 'Final Chapter MC Biker Territory', 'Inland Agricultural Flats'],
                'vehicle_types' => ['Monster Trucks', 'ATVs', 'Bagger Motorcycles', 'Crop Dusters'],
                'description' => 'Industrial heartland surrounding Lake Leonida where the Allied Crystal sugar refinery anchors local blue-collar life and the Final Chapter motorcycle club controls the rural roads.',
                'verification_status' => 'trailer_confirmed',
                'sort_order' => 5,
            ],
            [
                'name' => 'Mount Kalaga & Northern Uplands',
                'slug' => 'mount-kalaga-northern-uplands',
                'region_code' => 'MK-06',
                'real_world_inspiration' => 'Northern Florida / Georgia Border Pine Uplands',
                'biome_type' => 'Pine Forest & Canyon Highlands',
                'estimated_area_sq_km' => 12.50,
                'key_landmarks' => ['Mount Kalaga National Park Trails', 'Whitewater Kayak Rapids', 'Pine Backwoods', 'Hunting Ridges', 'Canyon Lookouts'],
                'vehicle_types' => ['4x4 Crawlers', 'Mountain Bikes', 'Kayaks', 'Bush Planes'],
                'description' => 'Confirmed northern wilderness border featuring Mount Kalaga National Park, whitewater kayak rivers, dense pine backwoods, and elevated hunting and off-road trails.',
                'verification_status' => 'official_art',
                'sort_order' => 6,
            ],
        ];

        foreach ($locations as $location) {
            MapLocation::query()->updateOrCreate(
                ['slug' => $location['slug']],
                $location
            );
        }
    }
}
