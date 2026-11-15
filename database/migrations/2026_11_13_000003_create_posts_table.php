<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->text('excerpt');
            $table->json('content_blocks');
            $table->longText('rendered_html')->nullable();
            $table->string('featured_image_label')->nullable();
            $table->string('featured_image_gradient')->default('from-pink-600 via-purple-700 to-indigo-950');
            $table->string('author_name')->default('Vice Telemetry Desk');
            $table->string('author_role')->default('Lead Technical Analyst');
            $table->string('status', 32)->default('draft')->index();
            $table->string('verification_badge', 48)->default('technical_projection')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedSmallInteger('reading_time_minutes')->default(5);
            $table->unsignedInteger('word_count')->default(0);
            $table->string('focus_keyword')->nullable()->index();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('og_title')->nullable();
            $table->string('og_description', 320)->nullable();
            $table->string('schema_type', 48)->default('BlogPosting');
            $table->json('faq_items')->nullable();
            $table->unsignedTinyInteger('seo_score')->default(0);
            $table->unsignedTinyInteger('readability_score')->default(0);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
