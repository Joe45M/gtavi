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
                'key_landmarks' => ['Ocean View Art Deco Strip', 'Vice International Airport', 'Venetian Causeway', 'Wynwood Murals District', 'Vice Port Cruise Terminal'],
                'vehicle_types' => ['Hypercars', 'Lowriders', 'Superyachts', 'Police Cruisers', 'SeaPlanes'],
                'description' => 'The glowing neon heart of Leonida. Features towering glass condos, Art Deco boulevards bathed in hot magenta signage, elevated Metromover transit, and deep-water marinas.',
                'verification_status' => 'trailer_confirmed',
                'sort_order' => 1,
            ],
            [
                'name' => 'Leonida Keys',
                'slug' => 'leonida-keys',
                'region_code' => 'LK-02',
                'real_world_inspiration' => 'Florida Keys & Seven Mile Bridge',
                'biome_type' => 'Tropical Archipelago & Coral Reef',
                'estimated_area_sq_km' => 19.20,
                'key_landmarks' => ['Seven-Span Overseas Viaduct', 'Gator Keys Marina', 'Tiki Sandbars', 'Deep-Channel Coast Guard Outpost'],
                'vehicle_types' => ['Speedboats', 'Jet Skis', 'Amphibious Planes', 'Convertibles'],
                'description' => 'A sun-bleached string of islands linked by iconic concrete highway bridges, featuring volumetric wave refraction, coral shoals, and smuggling coves.',
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
                'key_landmarks' => ['Airboat Outpost', 'Alligator Crossing Causeway', 'Sunken Radar Station', 'Cypress Canopy Channels'],
                'vehicle_types' => ['Fan Airboats', 'Lifted Mud Trucks', 'Hovercraft', 'Utility Helicopters'],
                'description' => 'Dense, dynamic wetland ecosystem showcased in Trailer 1 with murky water physics, dense mangrove visibility limits, and dynamic reptile wildlife.',
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
                'key_landmarks' => ['Port Gellhorn Pawn & Loan', 'Uncle Jack\'s Liquor Strip', 'Gellhorn Correctional Facility', 'Gulf Shipping Docks'],
                'vehicle_types' => ['Muscle Cars', 'Motocross Dirt Bikes', 'Cargo Semi-Trucks', 'Sheriff SUVs'],
                'description' => 'Gritty western coastal county marked by weathered motels, strip malls, pawn shops, and high-speed highway pursuits under Port Gellhorn Police jurisdiction.',
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
                'key_landmarks' => ['Allied Crystal Sugar Refinery', 'Lake Leonida Levee Loop', 'Thrillbilly Mud Club', 'Biker Roadhouse'],
                'vehicle_types' => ['Monster Trucks', 'ATVs', 'Bagger Motorcycles', 'Crop Dusters'],
                'description' => 'Vast inland basin blending industrial refineries, trailer parks, off-road mud parks, and high-speed levee straightaways linking east and west Leonida.',
                'verification_status' => 'official_art',
                'sort_order' => 5,
            ],
            [
                'name' => 'Mount Kalaga & Northern Uplands',
                'slug' => 'mount-kalaga-northern-uplands',
                'region_code' => 'MK-06',
                'real_world_inspiration' => 'Northern Florida / Georgia Border Pine Uplands',
                'biome_type' => 'Pine Forest & Canyon Highlands',
                'estimated_area_sq_km' => 12.50,
                'key_landmarks' => ['Kalaga National Park Trails', 'Kayak River Rapids', 'Firewatch Ridge', 'Hidden Airstrip'],
                'vehicle_types' => ['4x4 Crawlers', 'Mountain Bikes', 'Kayaks', 'Bush Planes'],
                'description' => 'Elevation contrast along the northern border providing dense pine canopies, vertical off-road trails, and long-range aerial vistas back toward Vice City.',
                'verification_status' => 'projected',
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
