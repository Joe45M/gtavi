<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_specs', function (Blueprint $table): void {
            $table->id();
            $table->string('platform_name');
            $table->string('slug')->unique();
            $table->string('platform_family', 32)->index();
            $table->string('target_resolution', 64);
            $table->string('target_fps', 48);
            $table->string('ray_tracing_tier', 120);
            $table->string('upscaling_tech', 120);
            $table->unsignedSmallInteger('estimated_download_gb');
            $table->unsignedSmallInteger('high_res_pack_gb')->default(0);
            $table->unsignedInteger('min_ssd_speed_mbps');
            $table->string('cpu_requirement');
            $table->string('gpu_requirement');
            $table->unsignedSmallInteger('ram_gb');
            $table->unsignedSmallInteger('vram_gb');
            $table->string('release_window', 80);
            $table->string('release_status', 48)->default('projected_window')->index();
            $table->text('notes')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_specs');
    }
};
