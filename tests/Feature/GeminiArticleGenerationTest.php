<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\GenerateGeminiArticleJob;
use App\Livewire\StudioPostEditor;
use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

final class GeminiArticleGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_daily_command_generates_both_news_and_evergreen_seo_articles(): void
    {
        Config::set('services.gemini.api_key', 'test-gemini-key-123');
        Config::set('services.gemini.model', 'gemini-flash-latest');

        $initialPostCount = Post::query()->count();

        $newsPayload = $this->buildLongFormArticlePayload(
            title: 'Take-Two Reaffirms GTA VI November 2026 Launch Window in Quarterly Call',
            slug: 'take-two-reaffirms-gta-vi-november-2026-launch-window',
            focusKeyword: 'GTA VI November 2026 launch',
            categorySlug: 'release-take-two-filings',
            schemaType: 'NewsArticle',
            verificationBadge: 'official_filing',
            targetParagraphWords: 920,
        );

        $evergreenPayload = $this->buildLongFormArticlePayload(
            title: 'GTA VI 60 FPS Performance Modes: PSSR & RAGE 9 Scaling on PS5 Pro',
            slug: 'gta-vi-60-fps-performance-modes-pssr-rage-9',
            focusKeyword: 'GTA VI 60 FPS',
            categorySlug: 'hardware-specs-download-math',
            schemaType: 'TechArticle',
            verificationBadge: 'technical_projection',
            targetParagraphWords: 940,
            interlinkSentence: 'Compare storage and decompression requirements in our [GTA VI download size guide](/gta/gta-vi-download-size-pre-load-math-ssd-guide).',
        );

        $callIndex = 0;
        $capturedPrompts = [];

        Http::fake(function (Request $request) use (&$callIndex, &$capturedPrompts, $newsPayload, $evergreenPayload) {
            $this->assertSame('test-gemini-key-123', $request->header('X-goog-api-key')[0] ?? '');
            $promptText = (string) ($request->data()['contents'][0]['parts'][0]['text'] ?? '');
            $this->assertStringContainsString('PREVIOUS 10 ARTICLES FOR CONTEXTUAL INTERLINKING', $promptText);
            $this->assertStringContainsString('/gta/gta-vi-download-size-pre-load-math-ssd-guide', $promptText);
            $capturedPrompts[] = $promptText;
            $callIndex++;

            if ($callIndex === 1) {
                $this->assertArrayHasKey('tools', $request->data());

                return Http::response([
                    'modelVersion' => 'gemini-2.5-flash',
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => json_encode($newsPayload, JSON_THROW_ON_ERROR)],
                                ],
                            ],
                            'groundingMetadata' => [
                                'webSearchQueries' => ['Take-Two GTA VI release'],
                            ],
                        ],
                    ],
                    'usageMetadata' => [
                        'promptTokenCount' => 1200,
                        'candidatesTokenCount' => 1800,
                        'thoughtsTokenCount' => 400,
                    ],
                ], 200);
            }

            $this->assertArrayNotHasKey('tools', $request->data());

            return Http::response([
                'modelVersion' => 'gemini-2.5-flash',
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($evergreenPayload, JSON_THROW_ON_ERROR)],
                            ],
                        ],
                    ],
                ],
                'usageMetadata' => [
                    'promptTokenCount' => 1000,
                    'candidatesTokenCount' => 1600,
                    'thoughtsTokenCount' => 200,
                ],
            ], 200);
        });

        $this->artisan('gta:generate-daily-articles')
            ->expectsOutputToContain('Total Estimated Run Cost: $0.045660 USD (6,200 tokens)')
            ->assertExitCode(Command::SUCCESS);

        Http::assertSentCount(2);
        $this->assertSame($initialPostCount + 2, Post::query()->count());

        $newsPost = Post::query()->where('slug', 'take-two-reaffirms-gta-vi-november-2026-launch-window')->firstOrFail();
        $evergreenPost = Post::query()->where('slug', 'gta-vi-60-fps-performance-modes-pssr-rage-9')->firstOrFail();

        $this->assertSame('NewsArticle', $newsPost->schema_type);
        $this->assertSame('TechArticle', $evergreenPost->schema_type);

        $this->assertCount(2, $capturedPrompts);

        $this->assertSame('gemini-2.5-flash', $newsPost->ai_model);
        $this->assertSame(1200, $newsPost->ai_prompt_tokens);
        $this->assertSame(1800, $newsPost->ai_completion_tokens);
        $this->assertSame(400, $newsPost->ai_thinking_tokens);
        $this->assertSame(1, $newsPost->ai_search_queries);
        $this->assertSame(3400, $newsPost->totalAiTokens());
        $this->assertEqualsWithDelta(0.04086, $newsPost->ai_estimated_cost_usd, 0.00001);
        $this->assertSame('$0.0409', $newsPost->formattedAiCost());

        $this->assertSame(1000, $evergreenPost->ai_prompt_tokens);
        $this->assertSame(1600, $evergreenPost->ai_completion_tokens);
        $this->assertSame(200, $evergreenPost->ai_thinking_tokens);
        $this->assertSame(0, $evergreenPost->ai_search_queries);
        $this->assertSame(2800, $evergreenPost->totalAiTokens());
        $this->assertEqualsWithDelta(0.0048, $evergreenPost->ai_estimated_cost_usd, 0.00001);
        $this->assertSame('$0.0048', $evergreenPost->formattedAiCost());

        $this->assertStringContainsString('/gta/gta-vi-download-size-pre-load-math-ssd-guide', (string) $evergreenPost->rendered_html);
        $this->assertMatchesRegularExpression('/href="[^"]*\/gta\/[a-z0-9-]+"/', (string) $newsPost->rendered_html);

        foreach ([$newsPost, $evergreenPost] as $post) {
            $this->assertSame('published', $post->status);
            $this->assertGreaterThanOrEqual(800, $post->word_count);
            $this->assertLessThanOrEqual(1500, $post->word_count);
            $this->assertGreaterThanOrEqual(95, $post->seo_score);
            $this->assertGreaterThanOrEqual(85, $post->readability_score);
            $this->assertNotEmpty($post->faq_items);
            $this->assertGreaterThanOrEqual(3, $post->tags()->count());
            $this->assertGreaterThanOrEqual(35, mb_strlen((string) $post->meta_title));
            $this->assertLessThanOrEqual(68, mb_strlen((string) $post->meta_title));
            $this->assertGreaterThanOrEqual(115, mb_strlen((string) $post->meta_description));
            $this->assertLessThanOrEqual(165, mb_strlen((string) $post->meta_description));

            $response = $this->get('/gta/'.$post->slug);
            $response->assertStatus(200);
            $response->assertSee($post->featured_image_label);
            $response->assertSee($post->schema_type);
            $response->assertSee('application/ld+json', false);
        }

        $admin = User::query()->where('email', 'admin@gtavi.pro')->firstOrFail();
        $studioResponse = $this->actingAs($admin)->get(route('studio.index'));
        $studioResponse->assertStatus(200);
        $studioResponse->assertSee('AI: $0.0409');
        $studioResponse->assertSee('AI: $0.0048');
    }

    public function test_normalizer_enforces_word_count_and_seo_bounds_on_short_gemini_payload(): void
    {
        Config::set('services.gemini.api_key', 'test-gemini-key-123');

        $minimalPayload = [
            'title' => 'Rockstar Reveals New Trailer 3 Details for Vice City',
            'focus_keyword' => 'GTA VI Trailer 3',
            'category_slug' => 'trailer-rage-9-analysis',
            'excerpt' => 'Short summary.',
            'meta_title' => 'Short',
            'meta_description' => 'Too short.',
            'content_blocks' => [
                [
                    'type' => 'paragraph',
                    'text' => str_repeat('Rockstar Games showcased dense crowds running across Vice Beach with realistic ray-traced lighting and fluid NPC AI behavior on PlayStation 5 hardware. ', 7).'Read more in our [GTA VI download telemetry report](/gta/non-existent-hallucinated-slug).',
                ],
            ],
        ];

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($minimalPayload, JSON_THROW_ON_ERROR)],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->artisan('gta:generate-daily-articles --type=news --status=draft')
            ->assertExitCode(Command::SUCCESS);

        $post = Post::query()->latest('id')->firstOrFail();

        $this->assertSame('draft', $post->status);
        $this->assertGreaterThan(0, $post->ai_prompt_tokens);
        $this->assertGreaterThan(0, $post->ai_completion_tokens);
        $this->assertGreaterThan(0.0, $post->ai_estimated_cost_usd);
        $this->assertNotNull($post->formattedAiCost());
        $this->assertGreaterThanOrEqual(800, $post->word_count);
        $this->assertLessThanOrEqual(1500, $post->word_count);
        $this->assertGreaterThanOrEqual(95, $post->seo_score);
        $this->assertGreaterThanOrEqual(85, $post->readability_score);
        $this->assertNotEmpty($post->faq_items);
        $this->assertStringContainsString('gta-vi-trailer-3', $post->slug);
        $this->assertStringNotContainsString('non-existent-hallucinated-slug', (string) $post->rendered_html);
        $this->assertMatchesRegularExpression('/href="[^"]*\/gta\/[a-z0-9-]+"/', (string) $post->rendered_html);

        $existingSlugs = Post::query()->pluck('slug')->all();
        preg_match_all('/href="[^"]*\/gta\/([a-z0-9-]+)"/', (string) $post->rendered_html, $slugMatches);
        $this->assertNotEmpty($slugMatches[1]);
        foreach ($slugMatches[1] as $linkedSlug) {
            $this->assertContains($linkedSlug, $existingSlugs);
        }

        foreach ($post->content_blocks as $block) {
            if (($block['type'] ?? '') === 'paragraph') {
                $this->assertLessThanOrEqual(110, str_word_count((string) ($block['text'] ?? '')));
            }
        }
    }

    public function test_command_fails_cleanly_when_gemini_api_key_is_missing(): void
    {
        Config::set('services.gemini.api_key', '');
        Http::fake();

        $this->artisan('gta:generate-daily-articles --type=evergreen')
            ->assertExitCode(Command::FAILURE);

        Http::assertNothingSent();
    }

    public function test_daily_article_schedule_is_registered(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('gta:generate-daily-articles')
            ->expectsOutputToContain('queue:work')
            ->assertExitCode(Command::SUCCESS);
    }

    public function test_admin_can_generate_published_and_draft_ai_articles_from_studio_dashboard(): void
    {
        Config::set('services.gemini.api_key', 'test-gemini-key-123');
        Config::set('services.gemini.model', 'gemini-flash-latest');
        Queue::fake();

        $admin = User::query()->where('email', 'admin@gtavi.pro')->firstOrFail();

        $publishResponse = $this->actingAs($admin)->post(route('studio.generate-ai'), [
            'mode' => 'evergreen',
            'topic' => 'Vice Port smuggling economy and yacht heist payouts',
            'status' => 'published',
        ]);

        $publishResponse->assertRedirect(route('studio.index'));
        $publishResponse->assertSessionHas('status');
        $this->assertStringContainsString('Generating 1 AI article(s) in the background', (string) session('status'));

        $draftResponse = $this->actingAs($admin)->post(route('studio.generate-ai'), [
            'mode' => 'news',
            'topic' => 'GTA VI Trailer 3 showcase announcement',
            'status' => 'draft',
        ]);

        $draftResponse->assertRedirect(route('studio.index'));
        $draftResponse->assertSessionHas('status');
        $this->assertStringContainsString('Generating 1 AI article(s) in the background', (string) session('status'));

        Queue::assertPushed(GenerateGeminiArticleJob::class, 2);
        Queue::assertPushed(GenerateGeminiArticleJob::class, static fn (GenerateGeminiArticleJob $job): bool => $job->mode === 'evergreen'
            && $job->topicOverride === 'Vice Port smuggling economy and yacht heist payouts'
            && $job->status === 'published');
        Queue::assertPushed(GenerateGeminiArticleJob::class, static fn (GenerateGeminiArticleJob $job): bool => $job->mode === 'news'
            && $job->topicOverride === 'GTA VI Trailer 3 showcase announcement'
            && $job->status === 'draft');

        $unauthorized = User::factory()->create();
        $this->actingAs($unauthorized)
            ->post(route('studio.generate-ai'), [
                'mode' => 'evergreen',
                'status' => 'draft',
            ])
            ->assertForbidden();

        $publishedPayload = $this->buildLongFormArticlePayload(
            title: 'GTA VI Vice Port Smuggling Economy & Yacht Heist Payouts',
            slug: 'gta-vi-vice-port-smuggling-economy-yacht-heist',
            focusKeyword: 'Vice Port smuggling',
            categorySlug: 'editions-pricing-pre-orders',
            schemaType: 'BlogPosting',
            verificationBadge: 'technical_projection',
            targetParagraphWords: 910,
        );

        $draftNewsPayload = $this->buildLongFormArticlePayload(
            title: 'Rockstar Sets GTA VI Trailer 3 Showcase Window for Leonida',
            slug: 'rockstar-sets-gta-vi-trailer-3-showcase-window',
            focusKeyword: 'GTA VI Trailer 3 showcase',
            categorySlug: 'release-take-two-filings',
            schemaType: 'NewsArticle',
            verificationBadge: 'confirmed',
            targetParagraphWords: 910,
        );

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::sequence()
                ->push([
                    'modelVersion' => 'gemini-2.5-flash',
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => json_encode($publishedPayload, JSON_THROW_ON_ERROR)],
                                ],
                            ],
                        ],
                    ],
                    'usageMetadata' => [
                        'promptTokenCount' => 1100,
                        'candidatesTokenCount' => 1700,
                        'thoughtsTokenCount' => 150,
                    ],
                ], 200)
                ->push([
                    'modelVersion' => 'gemini-2.5-flash',
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => json_encode($draftNewsPayload, JSON_THROW_ON_ERROR)],
                                ],
                            ],
                            'groundingMetadata' => [
                                'webSearchQueries' => ['GTA VI Trailer 3 showcase'],
                            ],
                        ],
                    ],
                    'usageMetadata' => [
                        'promptTokenCount' => 1250,
                        'candidatesTokenCount' => 1650,
                        'thoughtsTokenCount' => 250,
                    ],
                ], 200),
        ]);

        foreach (Queue::pushed(GenerateGeminiArticleJob::class) as $job) {
            app()->call([$job, 'handle']);
        }

        $publishedPost = Post::query()->where('slug', 'gta-vi-vice-port-smuggling-economy-yacht-heist')->firstOrFail();
        $this->assertSame('published', $publishedPost->status);
        $this->assertGreaterThanOrEqual(800, $publishedPost->word_count);
        $this->assertGreaterThanOrEqual(95, $publishedPost->seo_score);
        $this->assertGreaterThan(0.0, $publishedPost->ai_estimated_cost_usd);

        $draftPost = Post::query()->where('slug', 'rockstar-sets-gta-vi-trailer-3-showcase-window')->firstOrFail();
        $this->assertSame('draft', $draftPost->status);
        $this->assertSame('NewsArticle', $draftPost->schema_type);
        $this->assertSame(1, $draftPost->ai_search_queries);
    }

    public function test_studio_dashboard_displays_generating_banner_when_ai_jobs_are_queued(): void
    {
        $admin = User::query()->where('email', 'admin@gtavi.pro')->firstOrFail();

        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode(['displayName' => GenerateGeminiArticleJob::class], JSON_THROW_ON_ERROR),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->timestamp,
            'created_at' => now()->timestamp,
        ]);

        $this->actingAs($admin)
            ->get(route('studio.index'))
            ->assertOk()
            ->assertSee('Generating 1 AI article(s) in the background queue');
    }

    public function test_studio_dashboard_ai_generator_handles_missing_api_key_gracefully(): void
    {
        Config::set('services.gemini.api_key', '');
        Queue::fake();
        Http::fake();

        $admin = User::query()->where('email', 'admin@gtavi.pro')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('studio.generate-ai'), [
            'mode' => 'evergreen',
            'topic' => 'Test missing key handling',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('studio.index'));
        $response->assertSessionHasErrors('ai_generator');
        Queue::assertNothingPushed();
        Http::assertNothingSent();
    }

    public function test_editor_can_generate_ai_draft_directly_inside_studio_post_editor_livewire(): void
    {
        Config::set('services.gemini.api_key', 'test-gemini-key-123');
        Config::set('services.gemini.model', 'gemini-flash-latest');
        Queue::fake();

        $editor = User::query()->where('email', 'editor@gtavi.pro')->firstOrFail();

        $component = Livewire::actingAs($editor)
            ->test(StudioPostEditor::class)
            ->set('aiMode', 'evergreen')
            ->set('aiTopic', 'GTA VI crowd simulation and RAGE 9 NPC schedules')
            ->call('generateWithAi', 'draft')
            ->assertHasNoErrors();

        $this->assertStringContainsString('Generating EVERGREEN AI article (DRAFT) in the background', (string) $component->get('saveBanner'));
        Queue::assertPushed(GenerateGeminiArticleJob::class, static fn (GenerateGeminiArticleJob $job): bool => $job->mode === 'evergreen'
            && $job->topicOverride === 'GTA VI crowd simulation and RAGE 9 NPC schedules'
            && $job->status === 'draft');

        $livewirePayload = $this->buildLongFormArticlePayload(
            title: 'GTA VI Crowd Simulation & NPC Routines in Leonida',
            slug: 'gta-vi-crowd-simulation-npc-routines-leonida',
            focusKeyword: 'GTA VI crowd simulation',
            categorySlug: 'trailer-rage-9-analysis',
            schemaType: 'TechArticle',
            verificationBadge: 'technical_projection',
            targetParagraphWords: 910,
        );

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'modelVersion' => 'gemini-2.5-flash',
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($livewirePayload, JSON_THROW_ON_ERROR)],
                            ],
                        ],
                    ],
                ],
                'usageMetadata' => [
                    'promptTokenCount' => 980,
                    'candidatesTokenCount' => 1520,
                    'thoughtsTokenCount' => 180,
                ],
            ], 200),
        ]);

        foreach (Queue::pushed(GenerateGeminiArticleJob::class) as $job) {
            app()->call([$job, 'handle']);
        }

        $generated = Post::query()->where('slug', 'gta-vi-crowd-simulation-npc-routines-leonida')->firstOrFail();

        $this->assertSame('draft', $generated->status);
        $this->assertGreaterThanOrEqual(800, $generated->word_count);
        $this->assertGreaterThanOrEqual(95, $generated->seo_score);
        $this->assertGreaterThan(0.0, $generated->ai_estimated_cost_usd);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildLongFormArticlePayload(
        string $title,
        string $slug,
        string $focusKeyword,
        string $categorySlug,
        string $schemaType,
        string $verificationBadge,
        int $targetParagraphWords,
        string $interlinkSentence = '',
    ): array {
        $unitSentence = "Engineers analyzing {$focusKeyword} note 165 GB NVMe streaming overhead at 5,500 MB/s across PlayStation 5 Pro and Xbox Series X hardware ahead of November 19, 2026. ";
        $blocks = [
            [
                'type' => 'callout',
                'tone' => 'disclaimer',
                'title' => 'Independent Verification Notice',
                'text' => 'GTAVI.PRO separates official Rockstar Games and Take-Two Interactive filings from independent hardware modeling.',
            ],
            [
                'type' => 'paragraph',
                'drop_cap' => true,
                'text' => "Understanding {$focusKeyword} helps PlayStation 5, Xbox Series X, and future PC players prepare accurately for Grand Theft Auto VI across the State of Leonida. Rockstar Games confirmed November 19, 2026 for the console launch window. Explore updated milestone timelines on our [release tracker](/release-info).",
            ],
            [
                'type' => 'heading',
                'level' => 2,
                'text' => "How Does {$focusKeyword} Affect Console Performance?",
            ],
        ];

        $wordsAdded = 0;
        while ($wordsAdded < $targetParagraphWords) {
            $paragraph = trim($unitSentence.'RAGE 9 integrates ray-traced global illumination across Vice City, Leonida Keys, Grassrivers, Port Gellhorn, Ambrosia, and Mount Kalaga. Balanced 60 FPS modes prioritize clean PSSR upscaling alongside 16 GB GDDR6 memory stability.');
            $blocks[] = [
                'type' => 'paragraph',
                'text' => $paragraph,
            ];
            $wordsAdded += str_word_count($paragraph);
        }

        $blocks[] = [
            'type' => 'stat_grid',
            'title' => 'Key Benchmark Summary',
            'items' => [
                ['label' => 'Console Date', 'value' => 'Nov 19, 2026', 'note' => 'Confirmed global launch'],
                ['label' => 'NVMe Target', 'value' => '165 GB', 'note' => 'Projected install footprint'],
                ['label' => 'Frame Pacing', 'value' => '30–60 FPS', 'note' => 'Quality & performance presets'],
            ],
        ];

        $blocks[] = [
            'type' => 'heading',
            'level' => 2,
            'text' => 'What Should Players Prepare Before Launch Week?',
        ];
        $blocks[] = [
            'type' => 'paragraph',
            'text' => trim('Reserving 195 GB on PCIe 4.0 drives prevents storefront patch stalls when encrypted console packages unlock. Test fiber download windows inside our [interactive calculators](/calculators). '.$interlinkSentence),
        ];
        $blocks[] = [
            'type' => 'quote',
            'text' => 'Grand Theft Auto VI heads to the state of Leonida, home to Vice City and beyond.',
            'source' => 'Take-Two Interactive Investor Release',
            'cite_url' => 'https://www.rockstargames.com/VI',
        ];
        $blocks[] = [
            'type' => 'faq',
            'heading' => "{$focusKeyword} FAQs",
            'items' => [
                [
                    'question' => "When does {$focusKeyword} apply to console players?",
                    'answer' => 'All console readiness targets apply ahead of the November 19, 2026 release on PlayStation 5 and Xbox Series X|S.',
                ],
                [
                    'question' => 'Does GTAVI.PRO track official Rockstar filings?',
                    'answer' => 'Yes. Confirmed disclosures are tracked separately from independent hardware projections.',
                ],
            ],
        ];

        return [
            'title' => $title,
            'slug' => $slug,
            'subtitle' => "Detailed telemetry breakdown examining {$focusKeyword} for PlayStation 5 and Xbox Series X|S.",
            'excerpt' => "Analyze {$focusKeyword} benchmarks, NVMe headroom, and Rockstar Games milestones ahead of the November 19, 2026 launch.",
            'category_slug' => $categorySlug,
            'verification_badge' => $verificationBadge,
            'schema_type' => $schemaType,
            'focus_keyword' => $focusKeyword,
            'meta_title' => "{$focusKeyword} Guide & Benchmarks | GTAVI.PRO",
            'meta_description' => "Complete {$focusKeyword} technical guide covering Rockstar Games launch dates, PS5 Pro PSSR projections, NVMe speeds, and Leonida telemetry.",
            'og_title' => "{$focusKeyword} Guide & Benchmarks | GTAVI.PRO",
            'og_description' => "Complete {$focusKeyword} technical guide covering Rockstar Games launch dates and Leonida benchmarks.",
            'featured_image_label' => 'GEMINI TELEMETRY // '.strtoupper($focusKeyword),
            'featured_image_gradient' => 'from-cyan-500 via-purple-700 to-pink-700',
            'tags' => ['GTA VI', 'RAGE 9', 'PS5 Pro', 'Leonida'],
            'content_blocks' => $blocks,
        ];
    }
}
