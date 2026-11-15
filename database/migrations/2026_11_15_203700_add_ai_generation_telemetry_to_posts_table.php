<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->string('ai_model', 64)->nullable()->after('readability_score');
            $table->unsignedInteger('ai_prompt_tokens')->nullable()->after('ai_model');
            $table->unsignedInteger('ai_completion_tokens')->nullable()->after('ai_prompt_tokens');
            $table->unsignedInteger('ai_thinking_tokens')->nullable()->after('ai_completion_tokens');
            $table->unsignedSmallInteger('ai_search_queries')->nullable()->after('ai_thinking_tokens');
            $table->decimal('ai_estimated_cost_usd', 10, 6)->nullable()->after('ai_search_queries');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropColumn([
                'ai_model',
                'ai_prompt_tokens',
                'ai_completion_tokens',
                'ai_thinking_tokens',
                'ai_search_queries',
                'ai_estimated_cost_usd',
            ]);
        });
    }
};
