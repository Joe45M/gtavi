<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\GeminiArticleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

final class GenerateGeminiArticleJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 300;

    public int $tries = 2;

    public function __construct(
        public readonly string $mode = 'evergreen',
        public readonly ?string $topicOverride = null,
        public readonly string $status = 'published',
    ) {}

    public function handle(GeminiArticleService $geminiArticleService): void
    {
        $post = $geminiArticleService->generateAndCreatePost(
            mode: $this->mode,
            topicOverride: $this->topicOverride,
            status: $this->status,
        );

        Log::info('Queued Gemini AI article generated successfully.', [
            'post_id' => $post->id,
            'slug' => $post->slug,
            'mode' => $this->mode,
            'status' => $post->status,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Queued Gemini AI article generation failed.', [
            'mode' => $this->mode,
            'topic_override' => $this->topicOverride,
            'status' => $this->status,
            'message' => $exception?->getMessage(),
        ]);
    }
}
