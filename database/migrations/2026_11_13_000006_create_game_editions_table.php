<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_editions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline');
            $table->decimal('price_usd', 8, 2);
            $table->decimal('price_gbp', 8, 2);
            $table->decimal('price_eur', 8, 2);
            $table->decimal('price_cad', 8, 2);
            $table->decimal('price_aud', 8, 2);
            $table->unsignedInteger('price_jpy');
            $table->boolean('is_price_confirmed')->default(false);
            $table->unsignedSmallInteger('estimated_size_gb');
            $table->unsignedSmallInteger('early_access_hours')->default(0);
            $table->unsignedInteger('online_bonus_credits')->default(0);
            $table->json('physical_collectibles')->nullable();
            $table->json('digital_bonuses')->nullable();
            $table->string('badge_text', 64)->nullable();
            $table->boolean('is_highlighted')->default(false);
            $table->unsignedTinyInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_editions');
    }
};
