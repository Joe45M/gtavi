<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
            GameEditionSeeder::class,
            PlatformSpecSeeder::class,
            ReleaseMilestoneSeeder::class,
            MapLocationSeeder::class,
            PostSeeder::class,
        ]);
    }
}
