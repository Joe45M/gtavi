<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('release_milestones', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->date('milestone_date')->index();
            $table->string('display_date_label', 64);
            $table->string('category', 48)->index();
            $table->string('source_name', 120);
            $table->string('source_url')->nullable();
            $table->boolean('is_confirmed')->default(false)->index();
            $table->text('description');
            $table->string('impact_level', 32)->default('standard');
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('release_milestones');
    }
};
