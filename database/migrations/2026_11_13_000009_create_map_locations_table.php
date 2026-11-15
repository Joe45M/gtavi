<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('map_locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('region_code', 16);
            $table->string('real_world_inspiration');
            $table->string('biome_type', 64);
            $table->decimal('estimated_area_sq_km', 6, 2);
            $table->json('key_landmarks');
            $table->json('vehicle_types');
            $table->text('description');
            $table->string('verification_status', 48)->default('projected')->index();
            $table->unsignedTinyInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_locations');
    }
};
