<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\GeminiArticleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class GenerateDailyGtaArticlesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'gta:generate-daily-articles
                            {--type=both : Generation mode to run (both, news, or evergreen)}
                            {--topic= : Optional explicit subject or news headline override}
                            {--status=published : Initial post status (published or draft)}';

    /**
     * @var string
     */
    protected $description = 'Generate daily SEO-optimized GTA VI articles (latest news + evergreen topic) using Google Gemini.';

    public function handle(GeminiArticleService $geminiArticleService): int
    {
        $type = strtolower(trim((string) $this->option('type')));
        if (! in_array($type, ['both', 'news', 'evergreen'], true)) {
            $this->error("Invalid --type [{$type}]. Use 'both', 'news', or 'evergreen'.");

            return self::INVALID;
        }

        $status = strtolower(trim((string) $this->option('status')));
        if (! in_array($status, ['published', 'draft'], true)) {
            $this->error("Invalid --status [{$status}]. Use 'published' or 'draft'.");

            return self::INVALID;
        }

        $topicOption = trim((string) ($this->option('topic') ?? ''));
        $topicOverride = $topicOption !== '' ? $topicOption : null;

        $modes = $type === 'both' ? ['news', 'evergreen'] : [$type];
        $rows = [];
        $hasFailure = false;
        $totalTokens = 0;
        $totalCostUsd = 0.0;

        foreach ($modes as $mode) {
            $this->info("Requesting [{$mode}] article from Gemini API...");

            try {
                $post = $geminiArticleService->generateAndCreatePost(
                    mode: $mode,
                    topicOverride: $topicOverride,
                    status: $status
                );

                $postTokens = $post->totalAiTokens();
                $totalTokens += $postTokens;
                $totalCostUsd += (float) ($post->ai_estimated_cost_usd ?? 0.0);

                $rows[] = [
                    (string) $post->id,
                    strtoupper($mode),
                    Str::limit($post->title, 52),
                    $post->slug,
                    (string) $post->word_count,
                    $post->seo_score.'/100',
                    $post->readability_score.'/100',
                    number_format($postTokens),
                    $post->formattedAiCost() ?? '—',
                    $post->status,
                ];
            } catch (Throwable $exception) {
                $hasFailure = true;
                Log::error('Daily Gemini article generation failed.', [
                    'mode' => $mode,
                    'message' => $exception->getMessage(),
                ]);
                $this->error("Failed to generate [{$mode}] article: {$exception->getMessage()}");
            }
        }

        if ($rows !== []) {
            $this->table(
                ['ID', 'Type', 'Title', 'Slug', 'Words', 'SEO', 'Readability', 'Tokens', 'Est. Cost', 'Status'],
                $rows
            );
            $this->info(sprintf(
                'Total Estimated Run Cost: $%s USD (%s tokens)',
                number_format($totalCostUsd, 6),
                number_format($totalTokens)
            ));
        }

        return $hasFailure ? self::FAILURE : self::SUCCESS;
    }
}
