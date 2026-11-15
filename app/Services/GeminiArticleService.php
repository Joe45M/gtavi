<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;

final readonly class GeminiArticleService
{
    private const MIN_WORDS = 800;

    private const MAX_WORDS = 1500;

    /**
     * @var list<string>
     */
    private const ALLOWED_GRADIENTS = [
        'from-fuchsia-600 via-purple-800 to-slate-950',
        'from-pink-600 via-rose-700 to-purple-950',
        'from-purple-600 via-violet-800 to-fuchsia-950',
        'from-violet-600 via-purple-900 to-indigo-950',
        'from-rose-600 via-pink-700 to-purple-950',
        'from-cyan-500 via-purple-700 to-pink-700',
        'from-pink-600 via-purple-700 to-indigo-950',
    ];

    /**
     * @var list<string>
     */
    private const ALLOWED_BADGES = [
        'confirmed',
        'official_filing',
        'technical_projection',
        'community_speculation',
    ];

    /**
     * @var list<string>
     */
    private const ALLOWED_SCHEMA_TYPES = [
        'BlogPosting',
        'NewsArticle',
        'TechArticle',
    ];

    /**
     * Curated high-intent GTA VI evergreen subjects rotated across daily runs.
     *
     * @var list<array{topic: string, category_slug: string, focus_keyword: string}>
     */
    private const EVERGREEN_TOPICS = [
        [
            'topic' => 'GTA VI Console Performance Modes: 30 FPS vs 60 FPS PSSR Projections on PS5 & Xbox Series X',
            'category_slug' => 'hardware-specs-download-math',
            'focus_keyword' => 'GTA VI 60 FPS',
        ],
        [
            'topic' => 'State of Leonida Economy, Heist Payout Scaling & Property Mechanics Analysis',
            'category_slug' => 'editions-pricing-pre-orders',
            'focus_keyword' => 'GTA VI economy',
        ],
        [
            'topic' => 'Vice City Nightlife, Volumetric Weather & Coastal Boat Physics in Leonida',
            'category_slug' => 'vice-city-leonida-atlas',
            'focus_keyword' => 'Vice City map',
        ],
        [
            'topic' => 'Lucia & Jason Dual-Protagonist Switching, Inventory Slots & AI Companion Systems',
            'category_slug' => 'trailer-rage-9-analysis',
            'focus_keyword' => 'Lucia and Jason',
        ],
        [
            'topic' => 'GTA VI NPC AI Systems, Crowd Density & Law Enforcement Dispatch Logic on RAGE 9',
            'category_slug' => 'trailer-rage-9-analysis',
            'focus_keyword' => 'GTA VI AI',
        ],
        [
            'topic' => 'GTA VI Vehicle Handling Model, Suspension Physics & Racing Customization Guide',
            'category_slug' => 'trailer-rage-9-analysis',
            'focus_keyword' => 'GTA VI cars',
        ],
        [
            'topic' => 'GTA VI Online Evolution: Cross-Play Outlook, Dedicated Sessions & PS Plus / Game Pass Readiness',
            'category_slug' => 'release-take-two-filings',
            'focus_keyword' => 'GTA VI Online',
        ],
        [
            'topic' => 'Port Gellhorn & Ambrosia Deep Dive: Rural Leonida Highways, Motels & Industrial Landmarks',
            'category_slug' => 'vice-city-leonida-atlas',
            'focus_keyword' => 'Port Gellhorn',
        ],
        [
            'topic' => 'Mount Kalaga National Park & Grassrivers Wetlands: Wildlife, Hunting & Off-Road Traversal',
            'category_slug' => 'vice-city-leonida-atlas',
            'focus_keyword' => 'Mount Kalaga',
        ],
        [
            'topic' => 'PS5 Storage Expansion vs Xbox Seagate Cards: Best NVMe SSDs for GTA VI Launch Week',
            'category_slug' => 'hardware-specs-download-math',
            'focus_keyword' => 'PS5 SSD for GTA VI',
        ],
        [
            'topic' => 'Rockstar Games Collector Edition Physical Goods Precedent & Steelbook Availability Guide',
            'category_slug' => 'editions-pricing-pre-orders',
            'focus_keyword' => 'GTA VI Collector Edition',
        ],
        [
            'topic' => 'DualSense Haptics, Adaptive Triggers & 3D Tempest Audio Projections for GTA VI on PS5',
            'category_slug' => 'hardware-specs-download-math',
            'focus_keyword' => 'GTA VI PS5 DualSense',
        ],
    ];

    public function __construct(
        private PostService $postService,
        private ContentBlockService $contentBlockService,
    ) {}

    /**
     * Query the Google Gemini API and persist a fully SEO-optimized GTA VI article.
     *
     * @throws ConnectionException
     * @throws JsonException
     */
    public function generateAndCreatePost(
        string $mode = 'news',
        ?string $topicOverride = null,
        string $status = 'published',
    ): Post {
        if (! in_array($mode, ['news', 'evergreen'], true)) {
            throw new \InvalidArgumentException("Unsupported generation mode [{$mode}]. Expected 'news' or 'evergreen'.");
        }

        if (! in_array($status, ['published', 'draft'], true)) {
            throw new \InvalidArgumentException("Unsupported post status [{$status}]. Expected 'published' or 'draft'.");
        }

        $apiKey = trim((string) config('services.gemini.api_key', ''));
        if ($apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY is not configured. Set GEMINI_API_KEY in .env before generating articles.');
        }

        $categories = Category::query()->ordered()->get(['id', 'name', 'slug']);
        $existingPosts = Post::query()->latest('id')->get(['title', 'slug', 'focus_keyword']);

        $selectedEvergreen = $mode === 'evergreen'
            ? $this->selectNextEvergreenTopic($existingPosts->pluck('slug')->all(), $topicOverride)
            : null;

        $prompt = $this->buildPrompt(
            mode: $mode,
            topicOverride: $topicOverride,
            selectedEvergreen: $selectedEvergreen,
            existingTitles: $existingPosts->pluck('title')->all(),
            categories: $categories->map(fn (Category $cat): array => [
                'name' => $cat->name,
                'slug' => $cat->slug,
            ])->all()
        );

        $responseData = $this->callGeminiApi($apiKey, $prompt, $mode === 'news');
        $rawArticle = $this->extractJsonFromResponse($responseData);
        $groundingSources = $this->extractGroundingSources($responseData);
        $usage = $this->calculateUsageAndCost($responseData, $prompt, $mode === 'news');

        $normalized = $this->normalizeAndEnrichPayload(
            raw: $rawArticle,
            mode: $mode,
            fallbackTopic: $topicOverride ?: ($selectedEvergreen['topic'] ?? null),
            fallbackKeyword: $selectedEvergreen['focus_keyword'] ?? null,
            fallbackCategorySlug: $selectedEvergreen['category_slug'] ?? null,
            groundingSources: $groundingSources,
        );

        $normalized = array_merge($normalized, $usage);
        $normalized['status'] = $status;
        $normalized['published_at'] = $status === 'published' ? now() : null;

        $totalTokens = $usage['ai_prompt_tokens'] + $usage['ai_completion_tokens'] + $usage['ai_thinking_tokens'];

        return $this->postService->saveFromStudio(
            $normalized,
            null,
            sprintf(
                'Auto-generated daily (%s) via Gemini (%s) • %s tokens • est. $%s USD',
                $mode,
                $usage['ai_model'],
                number_format($totalTokens),
                number_format($usage['ai_estimated_cost_usd'], 4)
            )
        );
    }

    /**
     * Normalize Gemini output so it strictly meets 800-1500 words, Traditional SEO, AI GEO, and Readability standards.
     *
     * @param  array<string, mixed>  $raw
     * @param  array<int, array{title: string, url: string}>  $groundingSources
     * @return array<string, mixed>
     */
    public function normalizeAndEnrichPayload(
        array $raw,
        string $mode = 'news',
        ?string $fallbackTopic = null,
        ?string $fallbackKeyword = null,
        ?string $fallbackCategorySlug = null,
        array $groundingSources = [],
    ): array {
        $categoriesBySlug = Category::query()->pluck('id', 'slug');
        $defaultCategorySlug = $mode === 'news' ? 'release-take-two-filings' : 'trailer-rage-9-analysis';
        $requestedCategorySlug = trim((string) ($raw['category_slug'] ?? ''));

        $categoryId = $categoriesBySlug[$requestedCategorySlug]
            ?? $categoriesBySlug[$fallbackCategorySlug ?? '']
            ?? $categoriesBySlug[$defaultCategorySlug]
            ?? Category::query()->ordered()->value('id')
            ?? 1;

        $focusKeyword = $this->resolveFocusKeyword($raw, $fallbackKeyword, $fallbackTopic);

        $title = trim((string) ($raw['title'] ?? ''));
        if ($title === '') {
            $title = $fallbackTopic ?: "GTA VI {$focusKeyword} Analysis & Briefing";
        }
        if (! str_contains(mb_strtolower($title), mb_strtolower($focusKeyword))) {
            $title = "{$focusKeyword}: {$title}";
        }
        $title = Str::limit($title, 110, '');

        $slugBase = trim((string) ($raw['slug'] ?? ''));
        $slug = Str::slug($slugBase !== '' ? $slugBase : $title);
        $kwSlug = Str::slug($focusKeyword);
        if ($kwSlug !== '' && ! str_contains($slug, $kwSlug)) {
            $slug = trim("{$kwSlug}-{$slug}", '-');
        }
        $slug = trim(Str::limit($slug, 90, ''), '-');

        $subtitle = trim((string) ($raw['subtitle'] ?? ''));
        if ($subtitle === '') {
            $subtitle = "Independent GTAVI.PRO technical reporting covering {$focusKeyword}, verified filings, and hardware readiness ahead of November 19, 2026.";
        }

        $metaTitle = $this->normalizeMetaTitle((string) ($raw['meta_title'] ?? ''), $title, $focusKeyword);
        $metaDescription = $this->normalizeMetaDescription(
            (string) ($raw['meta_description'] ?? ''),
            (string) ($raw['excerpt'] ?? ''),
            $focusKeyword
        );

        $excerpt = trim((string) ($raw['excerpt'] ?? ''));
        if ($excerpt === '' || mb_strlen($excerpt) < 60) {
            $excerpt = $metaDescription;
        } elseif (! str_contains(mb_strtolower($excerpt), mb_strtolower($focusKeyword))) {
            $excerpt = "{$focusKeyword}: ".lcfirst($excerpt);
        }

        $verificationBadge = trim((string) ($raw['verification_badge'] ?? ''));
        if (! in_array($verificationBadge, self::ALLOWED_BADGES, true)) {
            $verificationBadge = $mode === 'news' ? 'confirmed' : 'technical_projection';
        }

        $schemaType = trim((string) ($raw['schema_type'] ?? ''));
        if (! in_array($schemaType, self::ALLOWED_SCHEMA_TYPES, true)) {
            $schemaType = $mode === 'news' ? 'NewsArticle' : 'TechArticle';
        }
        if ($mode === 'news') {
            $schemaType = 'NewsArticle';
        }

        $gradient = trim((string) ($raw['featured_image_gradient'] ?? ''));
        if (! in_array($gradient, self::ALLOWED_GRADIENTS, true)) {
            $gradient = self::ALLOWED_GRADIENTS[array_rand(self::ALLOWED_GRADIENTS)];
        }

        $featuredLabel = strtoupper(trim((string) ($raw['featured_image_label'] ?? '')));
        if ($featuredLabel === '') {
            $featuredLabel = $mode === 'news'
                ? 'NEWS BRIEFING // '.strtoupper(Str::limit($focusKeyword, 28, ''))
                : 'TECHNICAL GUIDE // '.strtoupper(Str::limit($focusKeyword, 28, ''));
        }
        $featuredLabel = Str::limit($featuredLabel, 110, '');

        $tags = $this->normalizeTags($raw['tags'] ?? [], $focusKeyword, $mode);

        $blocks = $this->normalizeBlocks(
            rawBlocks: is_array($raw['content_blocks'] ?? null) ? $raw['content_blocks'] : [],
            focusKeyword: $focusKeyword,
            mode: $mode,
            title: $title,
            groundingSources: $groundingSources,
            sourceName: trim((string) ($raw['source_name'] ?? '')),
            sourceUrl: trim((string) ($raw['source_url'] ?? ''))
        );

        $blocks = $this->enforceWordCountBounds($blocks, $focusKeyword, $mode, $title);

        return [
            'category_id' => (int) $categoryId,
            'title' => $title,
            'slug' => $slug,
            'subtitle' => Str::limit($subtitle, 240, ''),
            'excerpt' => Str::limit($excerpt, 320, ''),
            'content_blocks' => $blocks,
            'featured_image_label' => $featuredLabel,
            'featured_image_gradient' => $gradient,
            'author_name' => 'joe',
            'author_role' => $mode === 'news' ? 'AI Newsroom & Lead Analyst' : 'Founder & Lead Analyst',
            'verification_badge' => $verificationBadge,
            'is_featured' => false,
            'focus_keyword' => $focusKeyword,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'og_title' => $this->normalizeMetaTitle((string) ($raw['og_title'] ?? ''), $metaTitle, $focusKeyword),
            'og_description' => $metaDescription,
            'schema_type' => $schemaType,
            'tags' => $tags,
        ];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     */
    private function callGeminiApi(string $apiKey, string $prompt, bool $enableGoogleSearch): array
    {
        $endpoint = rtrim((string) config('services.gemini.endpoint', 'https://generativelanguage.googleapis.com/v1beta/models'), '/');
        $model = trim((string) config('services.gemini.model', 'gemini-flash-latest'));
        $timeout = (int) config('services.gemini.timeout', 180);

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => 0.65,
            ],
        ];

        if ($enableGoogleSearch) {
            $payload['tools'] = [
                ['google_search' => new \stdClass],
            ];
        }

        $response = Http::withHeaders([
            'X-goog-api-key' => $apiKey,
            'Content-Type' => 'application/json',
        ])
            ->timeout($timeout)
            ->post("{$endpoint}/{$model}:generateContent", $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                sprintf('Gemini API request failed with HTTP %d: %s', $response->status(), Str::limit($response->body(), 400))
            );
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new RuntimeException('Gemini API returned an invalid JSON response body.');
        }

        return $json;
    }

    /**
     * @param  array<string, mixed>  $responseData
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    private function extractJsonFromResponse(array $responseData): array
    {
        $text = (string) data_get($responseData, 'candidates.0.content.parts.0.text', '');
        if ($text === '') {
            $parts = data_get($responseData, 'candidates.0.content.parts', []);
            if (is_array($parts)) {
                foreach ($parts as $part) {
                    if (is_array($part) && is_string($part['text'] ?? null) && trim($part['text']) !== '') {
                        $text = $part['text'];
                        break;
                    }
                }
            }
        }

        $cleaned = trim($text);
        if (str_starts_with($cleaned, '```')) {
            $cleaned = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $cleaned) ?? $cleaned;
            $cleaned = trim($cleaned);
        }

        if ($cleaned === '') {
            throw new RuntimeException('Gemini API returned empty article content.');
        }

        $decoded = json_decode($cleaned, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($decoded)) {
            throw new RuntimeException('Gemini article payload decoded to non-array JSON.');
        }

        return $decoded;
    }

    /**
     * @param  array<string, mixed>  $responseData
     * @return array<int, array{title: string, url: string}>
     */
    private function extractGroundingSources(array $responseData): array
    {
        $chunks = data_get($responseData, 'candidates.0.groundingMetadata.groundingChunks', []);
        if (! is_array($chunks)) {
            return [];
        }

        $sources = [];
        foreach ($chunks as $chunk) {
            $url = trim((string) data_get($chunk, 'web.uri', ''));
            $title = trim((string) data_get($chunk, 'web.title', ''));
            if ($url !== '' && ! str_contains($url, 'vertexaisearch.cloud.google.com')) {
                $sources[] = [
                    'title' => $title !== '' ? $title : 'Verified External Report',
                    'url' => $url,
                ];
            }
        }

        return array_slice($sources, 0, 3);
    }

    /**
     * @param  array<int, string>  $existingSlugs
     * @return array{topic: string, category_slug: string, focus_keyword: string}|null
     */
    private function selectNextEvergreenTopic(array $existingSlugs, ?string $topicOverride): ?array
    {
        if ($topicOverride !== null && trim($topicOverride) !== '') {
            return [
                'topic' => trim($topicOverride),
                'category_slug' => 'trailer-rage-9-analysis',
                'focus_keyword' => Str::words(trim($topicOverride), 4, ''),
            ];
        }

        $slugBlob = mb_strtolower(implode(' ', $existingSlugs));
        foreach (self::EVERGREEN_TOPICS as $candidate) {
            $kwSlug = Str::slug($candidate['focus_keyword']);
            if ($kwSlug !== '' && ! str_contains($slugBlob, $kwSlug)) {
                return $candidate;
            }
        }

        $dayIndex = (int) now()->dayOfYear % count(self::EVERGREEN_TOPICS);

        return self::EVERGREEN_TOPICS[$dayIndex];
    }

    /**
     * @param  array{topic: string, category_slug: string, focus_keyword: string}|null  $selectedEvergreen
     * @param  array<int, string>  $existingTitles
     * @param  array<int, array{name: string, slug: string}>  $categories
     */
    private function buildPrompt(
        string $mode,
        ?string $topicOverride,
        ?array $selectedEvergreen,
        array $existingTitles,
        array $categories,
    ): string {
        $today = now()->format('F j, Y');
        $existingList = $existingTitles !== []
            ? implode("\n- ", array_slice($existingTitles, 0, 20))
            : 'None yet';

        $categoryLines = implode("\n- ", array_map(
            static fn (array $cat): string => "{$cat['slug']} ({$cat['name']})",
            $categories
        ));

        $gradientsList = implode(' | ', self::ALLOWED_GRADIENTS);

        $mission = $mode === 'news'
            ? ($topicOverride !== null && trim($topicOverride) !== ''
                ? "ARTICLE MISSION (SPECIFIC NEWS DISPATCH): Use Google Search to research and write a breaking news analysis specifically about: \"{$topicOverride}\". Focus strictly on that concrete news event with verified facts, dates, quotes, and what it means for GTA VI players."
                : "ARTICLE MISSION (DAILY NEWS DISPATCH): Use Google Search to find the single most noteworthy, specific recent GTA VI, Rockstar Games, or Take-Two Interactive news story as of {$today} that is NOT already covered by the existing headlines below. Target that ONE specific news item deeply with factual attribution.")
            : sprintf(
                'ARTICLE MISSION (EVERGREEN GTA VI DEEP DIVE): Write an authoritative, evergreen GTA VI guide on the subject: "%s" (suggested primary focus keyword: "%s"). Do NOT duplicate existing headlines.',
                $topicOverride ?: ($selectedEvergreen['topic'] ?? 'GTA VI Gameplay Systems & Leonida Technical Architecture'),
                $selectedEvergreen['focus_keyword'] ?? 'GTA VI guide'
            );

        return <<<PROMPT
You are the senior technical editor at GTAVI.PRO, an independent fan newsroom and hardware telemetry desk (never affiliated with Rockstar Games or Take-Two Interactive).
Today's date is {$today}. Confirmed launch timeline context: Rockstar Games confirmed Grand Theft Auto VI launches on November 19, 2026 for PlayStation 5 and Xbox Series X|S, set across the State of Leonida (Vice City, Leonida Keys, Grassrivers, Port Gellhorn, Ambrosia, Mount Kalaga). Never present unannounced rumors (such as unconfirmed PC dates, exact file sizes, or unconfirmed MSRPs) as official facts—label projections clearly.

{$mission}

EXISTING SITE HEADLINES (DO NOT DUPLICATE OR CLOSELY OVERLAP):
- {$existingList}

VALID CATEGORY SLUGS (choose the single best match):
- {$categoryLines}

STRICT LENGTH & SEO / AI-GEO RULES:
1. Word Count: The combined text across all `content_blocks` MUST be between 900 and 1,350 words (strictly within 800-1,500 words).
2. Focus Keyword Placement: Pick one clear 2-4 word `focus_keyword`. Include the exact `focus_keyword` verbatim inside:
   - `title` (H1)
   - `slug`
   - `meta_description`
   - The VERY FIRST `paragraph` block
   - At least one `heading` block (level 2)
3. Meta Sizes: `meta_title` must be 45-64 characters ending with `| GTAVI.PRO`. `meta_description` must be 130-158 characters.
4. Readability & Scannability:
   - Keep sentences crisp (average 15-21 words per sentence).
   - Every `paragraph` block MUST stay between 55 and 95 words (never exceed 100 words in a single paragraph block).
   - Include 4 to 6 `heading` blocks (level 2 or 3), with at least two phrased as clear AI-answer headings starting with How, Why, What, or When (and ending with `?`).
5. Rich Structured Blocks & Information Gain:
   - Include at least 1 `callout` (`tone`: `"disclaimer"` or `"info"`), 1 `stat_grid` (3 numeric items with real units like GB, MB/s, FPS, km², $, %, 2026), 1 `spec_table`, 1 `quote` (external source attribution if citing news or Rockstar filings), 1 `calculator_embed` (`calculator_type` one of `download`, `pc`, `edition`, `map`), and 1 `faq` block with 3 concise Q&A items.
   - Include 2-3 natural Markdown internal links inside paragraph text pointing to `/release-info`, `/download-sizes`, `/prices-editions`, `/map-atlas`, `/calculators`, or `/gta`.
   - Ground the article across concrete GTA VI entities (Grand Theft Auto VI, Rockstar Games, Take-Two Interactive, Leonida, Vice City, PS5, Xbox Series X|S, RAGE 9).
6. Visual Card Metadata:
   - Set `featured_image_label` (max 55 uppercase characters, e.g., "NEWS BRIEFING // NOVEMBER 2026 TELEMETRY").
   - Pick `featured_image_gradient` strictly from: {$gradientsList}

OUTPUT FORMAT: Return a single valid JSON object with EXACTLY these keys:
{
  "title": "string",
  "slug": "kebab-case-string",
  "subtitle": "concise 1-sentence standfirst summary",
  "excerpt": "130-180 char summary",
  "category_slug": "one valid slug from the list above",
  "verification_badge": "confirmed | official_filing | technical_projection | community_speculation",
  "schema_type": "NewsArticle | TechArticle | BlogPosting",
  "focus_keyword": "2-4 word focus keyword",
  "meta_title": "45-64 chars ending with | GTAVI.PRO",
  "meta_description": "130-158 chars containing exact focus_keyword",
  "og_title": "45-64 chars",
  "og_description": "130-158 chars",
  "featured_image_label": "UPPERCASE SHORT LABEL",
  "featured_image_gradient": "one of the allowed gradient strings",
  "source_name": "Primary news/filing publisher name if news",
  "source_url": "Clean HTTPS source URL if known, else empty string",
  "tags": ["Tag One", "Tag Two", "Tag Three", "Tag Four"],
  "content_blocks": [
    {"type": "callout", "tone": "disclaimer", "title": "...", "text": "..."},
    {"type": "paragraph", "drop_cap": true, "text": "..."},
    {"type": "stat_grid", "title": "...", "items": [{"label": "...", "value": "...", "note": "..."}]},
    {"type": "heading", "level": 2, "text": "..."},
    {"type": "paragraph", "text": "..."},
    {"type": "spec_table", "caption": "...", "headers": ["...", "...", "..."], "rows": [["...", "...", "..."]]},
    {"type": "quote", "text": "...", "source": "...", "cite_url": "https://..."},
    {"type": "calculator_embed", "calculator_type": "download", "title": "...", "summary": "...", "cta_label": "Open Calculator"},
    {"type": "faq", "heading": "Frequently Asked Questions", "items": [{"question": "...", "answer": "..."}]}
  ]
}
PROMPT;
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    private function resolveFocusKeyword(array $raw, ?string $fallbackKeyword, ?string $fallbackTopic): string
    {
        $candidate = trim((string) ($raw['focus_keyword'] ?? ''));
        if ($candidate === '' && $fallbackKeyword !== null && trim($fallbackKeyword) !== '') {
            $candidate = trim($fallbackKeyword);
        }
        if ($candidate === '' && $fallbackTopic !== null && trim($fallbackTopic) !== '') {
            $candidate = Str::words(trim($fallbackTopic), 4, '');
        }
        if ($candidate === '') {
            $candidate = Str::words(trim((string) ($raw['title'] ?? 'GTA VI analysis')), 3, '');
        }

        $candidate = preg_replace('/[^\pL\pN\s\-]/u', '', $candidate) ?? $candidate;
        $candidate = trim(preg_replace('/\s+/', ' ', $candidate) ?? 'GTA VI update');

        return Str::limit($candidate !== '' ? $candidate : 'GTA VI update', 45, '');
    }

    private function normalizeMetaTitle(string $rawMetaTitle, string $title, string $focusKeyword): string
    {
        $candidate = trim($rawMetaTitle) !== '' ? trim($rawMetaTitle) : trim($title);
        $base = trim((string) preg_replace('/\s*\|\s*GTAVI\.PRO\s*$/i', '', $candidate));

        if (! str_contains(mb_strtolower($base), mb_strtolower($focusKeyword))) {
            $base = "{$focusKeyword} — {$base}";
        }

        $maxBaseLen = 56;
        if (mb_strlen($base) > $maxBaseLen) {
            $base = Str::words($base, 8, '');
            $base = rtrim(mb_substr($base, 0, $maxBaseLen));
        }

        $result = "{$base} | GTAVI.PRO";
        if (mb_strlen($result) < 35) {
            $result = "{$base} — GTA VI Technical Analysis | GTAVI.PRO";
        }
        if (mb_strlen($result) > 68) {
            $result = rtrim(mb_substr($base, 0, $maxBaseLen)).' | GTAVI.PRO';
        }
        if (mb_strlen($result) < 35) {
            $result = Str::limit("{$result} Dispatch Guide", 68, '');
        }

        return $result;
    }

    private function normalizeMetaDescription(string $rawMetaDesc, string $excerpt, string $focusKeyword): string
    {
        $desc = trim($rawMetaDesc) !== '' ? trim($rawMetaDesc) : trim($excerpt);
        $desc = trim(preg_replace('/\s+/', ' ', $desc) ?? '');

        if ($desc === '') {
            $desc = "Detailed GTAVI.PRO report analyzing {$focusKeyword}, verified Rockstar Games announcements, and Leonida telemetry benchmarks for November 19, 2026.";
        } elseif (! str_contains(mb_strtolower($desc), mb_strtolower($focusKeyword))) {
            $desc = "{$focusKeyword}: ".lcfirst($desc);
        }

        if (mb_strlen($desc) < 115) {
            $desc = rtrim($desc, '. ').'. Explore verified Rockstar Games timelines, PS5 and Xbox specs, and interactive GTA VI tools at GTAVI.PRO.';
        }

        if (mb_strlen($desc) > 165) {
            $truncated = mb_substr($desc, 0, 162);
            $lastSpace = mb_strrpos($truncated, ' ');
            $desc = ($lastSpace !== false && $lastSpace > 120 ? mb_substr($truncated, 0, $lastSpace) : $truncated).'...';
        }

        return $desc;
    }

    /**
     * @return list<string>
     */
    private function normalizeTags(mixed $rawTags, string $focusKeyword, string $mode): array
    {
        $tags = [];
        if (is_array($rawTags)) {
            foreach ($rawTags as $tag) {
                $clean = trim((string) $tag);
                if ($clean !== '' && mb_strlen($clean) <= 40) {
                    $tags[] = $clean;
                }
            }
        }

        if ($tags === []) {
            $tags = $mode === 'news'
                ? ['GTA VI News', 'Rockstar Games', 'Release Watch']
                : ['GTA VI Guide', 'State of Leonida', 'Technical Analysis'];
        }

        if (count($tags) < 3 && ! in_array($focusKeyword, $tags, true)) {
            $tags[] = Str::limit($focusKeyword, 32, '');
        }

        return array_values(array_slice(array_unique($tags), 0, 5));
    }

    /**
     * @param  array<int, mixed>  $rawBlocks
     * @param  array<int, array{title: string, url: string}>  $groundingSources
     * @return array<int, array<string, mixed>>
     */
    private function normalizeBlocks(
        array $rawBlocks,
        string $focusKeyword,
        string $mode,
        string $title,
        array $groundingSources,
        string $sourceName,
        string $sourceUrl,
    ): array {
        $blocks = [];

        foreach ($rawBlocks as $block) {
            if (! is_array($block)) {
                continue;
            }

            $type = trim((string) ($block['type'] ?? 'paragraph'));
            $normalizedItems = match ($type) {
                'heading' => $this->buildHeadingBlock($block),
                'paragraph' => $this->splitParagraphBlock($block),
                'callout' => $this->buildCalloutBlock($block),
                'stat_grid' => $this->buildStatGridBlock($block),
                'spec_table' => $this->buildSpecTableBlock($block),
                'faq' => $this->buildFaqBlock($block),
                'quote' => $this->buildQuoteBlock($block),
                'calculator_embed' => $this->buildCalculatorBlock($block),
                'code' => $this->buildCodeBlock($block),
                default => $this->splitParagraphBlock($block),
            };

            foreach ($normalizedItems as $item) {
                $blocks[] = $item;
            }
        }

        $blocks = $this->ensureLeadCalloutAndIntro($blocks, $focusKeyword, $mode, $title);
        $blocks = $this->ensureHeadingSignals($blocks, $focusKeyword, $title);
        $blocks = $this->ensureStructuredAndGeoSignals($blocks, $focusKeyword, $mode, $groundingSources, $sourceName, $sourceUrl);

        return array_values($blocks);
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<int, array<string, mixed>>
     */
    private function buildHeadingBlock(array $block): array
    {
        $text = trim((string) ($block['text'] ?? ''));
        if ($text === '') {
            return [];
        }

        $level = (int) ($block['level'] ?? 2);

        return [[
            'id' => (string) Str::uuid(),
            'type' => 'heading',
            'level' => $level === 3 ? 3 : 2,
            'text' => $text,
        ]];
    }

    /**
     * Split overlong paragraphs into concise <= 95-word blocks so readability checks consistently pass.
     *
     * @param  array<string, mixed>  $block
     * @return array<int, array<string, mixed>>
     */
    private function splitParagraphBlock(array $block): array
    {
        $text = trim((string) ($block['text'] ?? ''));
        if ($text === '') {
            return [];
        }

        $isFirstDropCap = (bool) ($block['drop_cap'] ?? false);
        if (str_word_count($text) <= 95) {
            return [[
                'id' => (string) Str::uuid(),
                'type' => 'paragraph',
                'drop_cap' => $isFirstDropCap,
                'text' => $text,
            ]];
        }

        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [$text];
        $chunks = [];
        $current = [];
        $currentWords = 0;

        foreach ($sentences as $sentence) {
            $words = str_word_count($sentence);
            if ($current !== [] && ($currentWords + $words) > 90) {
                $chunks[] = implode(' ', $current);
                $current = [$sentence];
                $currentWords = $words;
            } else {
                $current[] = $sentence;
                $currentWords += $words;
            }
        }

        if ($current !== []) {
            $chunks[] = implode(' ', $current);
        }

        $output = [];
        foreach ($chunks as $idx => $chunkText) {
            $output[] = [
                'id' => (string) Str::uuid(),
                'type' => 'paragraph',
                'drop_cap' => $isFirstDropCap && $idx === 0,
                'text' => $chunkText,
            ];
        }

        return $output;
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<int, array<string, mixed>>
     */
    private function buildCalloutBlock(array $block): array
    {
        $text = trim((string) ($block['text'] ?? ''));
        if ($text === '') {
            return [];
        }

        $tone = trim((string) ($block['tone'] ?? 'info'));

        return [[
            'id' => (string) Str::uuid(),
            'type' => 'callout',
            'tone' => in_array($tone, ['info', 'disclaimer', 'confirmed'], true) ? $tone : 'info',
            'title' => trim((string) ($block['title'] ?? 'Editorial Context')) ?: 'Editorial Context',
            'text' => $text,
        ]];
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<int, array<string, mixed>>
     */
    private function buildStatGridBlock(array $block): array
    {
        $items = [];
        foreach (($block['items'] ?? []) as $item) {
            if (! is_array($item)) {
                continue;
            }
            $label = trim((string) ($item['label'] ?? ''));
            $value = trim((string) ($item['value'] ?? ''));
            if ($label !== '' && $value !== '') {
                $items[] = [
                    'label' => $label,
                    'value' => $value,
                    'note' => trim((string) ($item['note'] ?? '')),
                ];
            }
        }

        if ($items === []) {
            return [];
        }

        return [[
            'id' => (string) Str::uuid(),
            'type' => 'stat_grid',
            'title' => trim((string) ($block['title'] ?? 'Key Telemetry Metrics')) ?: 'Key Telemetry Metrics',
            'items' => array_slice($items, 0, 4),
        ]];
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<int, array<string, mixed>>
     */
    private function buildSpecTableBlock(array $block): array
    {
        $headers = array_values(array_filter(
            array_map(static fn (mixed $h): string => trim((string) $h), is_array($block['headers'] ?? null) ? $block['headers'] : []),
            static fn (string $h): bool => $h !== ''
        ));

        $rows = [];
        foreach (($block['rows'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $cells = array_map(static fn (mixed $cell): string => trim((string) $cell), $row);
            if (array_filter($cells) !== []) {
                $rows[] = $cells;
            }
        }

        if ($headers === [] || $rows === []) {
            return [];
        }

        return [[
            'id' => (string) Str::uuid(),
            'type' => 'spec_table',
            'caption' => trim((string) ($block['caption'] ?? 'GTA VI Reference Matrix')) ?: 'GTA VI Reference Matrix',
            'headers' => $headers,
            'rows' => $rows,
        ]];
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<int, array<string, mixed>>
     */
    private function buildFaqBlock(array $block): array
    {
        $items = [];
        foreach (($block['items'] ?? []) as $item) {
            if (! is_array($item)) {
                continue;
            }
            $question = trim((string) ($item['question'] ?? ''));
            $answer = trim((string) ($item['answer'] ?? ''));
            if ($question !== '' && $answer !== '') {
                $items[] = [
                    'question' => $question,
                    'answer' => $answer,
                ];
            }
        }

        if ($items === []) {
            return [];
        }

        return [[
            'id' => (string) Str::uuid(),
            'type' => 'faq',
            'heading' => trim((string) ($block['heading'] ?? 'Frequently Asked Questions')) ?: 'Frequently Asked Questions',
            'items' => array_slice($items, 0, 5),
        ]];
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<int, array<string, mixed>>
     */
    private function buildQuoteBlock(array $block): array
    {
        $text = trim((string) ($block['text'] ?? ''));
        if ($text === '') {
            return [];
        }

        $citeUrl = trim((string) ($block['cite_url'] ?? ''));
        if (str_contains($citeUrl, 'vertexaisearch.cloud.google.com') || ! preg_match('/^https?:\/\//i', $citeUrl)) {
            $citeUrl = '';
        }

        return [[
            'id' => (string) Str::uuid(),
            'type' => 'quote',
            'text' => $text,
            'source' => trim((string) ($block['source'] ?? 'Industry Filing & Public Statement')) ?: 'Industry Filing & Public Statement',
            'cite_url' => $citeUrl !== '' ? $citeUrl : 'https://gtavi.pro/release-info',
        ]];
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<int, array<string, mixed>>
     */
    private function buildCalculatorBlock(array $block): array
    {
        $calcType = trim((string) ($block['calculator_type'] ?? 'download'));
        if (! in_array($calcType, ['download', 'pc', 'edition', 'map'], true)) {
            $calcType = 'download';
        }

        return [[
            'id' => (string) Str::uuid(),
            'type' => 'calculator_embed',
            'calculator_type' => $calcType,
            'title' => trim((string) ($block['title'] ?? 'Run Your GTA VI Hardware & Launch Numbers')) ?: 'Run Your GTA VI Hardware & Launch Numbers',
            'summary' => trim((string) ($block['summary'] ?? 'Compare console storage headroom, download times, and regional editions interactively.')) ?: 'Compare console storage headroom, download times, and regional editions interactively.',
            'cta_label' => trim((string) ($block['cta_label'] ?? 'Open GTA VI Calculator')) ?: 'Open GTA VI Calculator',
        ]];
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<int, array<string, mixed>>
     */
    private function buildCodeBlock(array $block): array
    {
        $code = trim((string) ($block['code'] ?? ''));
        if ($code === '') {
            return [];
        }

        return [[
            'id' => (string) Str::uuid(),
            'type' => 'code',
            'caption' => trim((string) ($block['caption'] ?? 'Reference Data')),
            'language' => trim((string) ($block['language'] ?? 'json')) ?: 'json',
            'code' => $code,
        ]];
    }

    /**
     * Ensure lead disclaimer/context callout exists and the first paragraph embeds the exact focus keyword.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    private function ensureLeadCalloutAndIntro(array $blocks, string $focusKeyword, string $mode, string $title): array
    {
        $hasCallout = collect($blocks)->contains(fn (array $b): bool => ($b['type'] ?? '') === 'callout');
        if (! $hasCallout) {
            array_unshift($blocks, [
                'id' => (string) Str::uuid(),
                'type' => 'callout',
                'tone' => 'disclaimer',
                'title' => $mode === 'news' ? 'GTAVI.PRO Independent News Verification' : 'Independent Technical Briefing',
                'text' => 'GTAVI.PRO is an independent fan publication not affiliated with Rockstar Games or Take-Two Interactive. Official milestones reflect verified public disclosures, while unannounced specifications remain clearly labeled estimates.',
            ]);
        }

        $firstParagraphIndex = null;
        foreach ($blocks as $idx => $block) {
            if (($block['type'] ?? '') === 'paragraph') {
                $firstParagraphIndex = $idx;
                break;
            }
        }

        if ($firstParagraphIndex === null) {
            $blocks[] = [
                'id' => (string) Str::uuid(),
                'type' => 'paragraph',
                'drop_cap' => true,
                'text' => "Today's technical report on **{$focusKeyword}** breaks down what matters most for Grand Theft Auto VI players preparing for the November 19, 2026 console release across the State of Leonida.",
            ];

            return $blocks;
        }

        $introText = (string) $blocks[$firstParagraphIndex]['text'];
        if (! str_contains(mb_strtolower($introText), mb_strtolower($focusKeyword))) {
            $blocks[$firstParagraphIndex]['text'] = "Understanding **{$focusKeyword}** is essential context around {$title}. {$introText}";
        }
        $blocks[$firstParagraphIndex]['drop_cap'] = true;

        return $blocks;
    }

    /**
     * Ensure >= 2 headings, focus keyword in at least one H2/H3, and AEO question-ready heading.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    private function ensureHeadingSignals(array $blocks, string $focusKeyword, string $title): array
    {
        $toc = $this->contentBlockService->extractTableOfContents($blocks);

        $hasKeywordHeading = collect($toc)->contains(
            fn (array $h): bool => str_contains(mb_strtolower($h['text']), mb_strtolower($focusKeyword))
        );
        if (! $hasKeywordHeading) {
            $blocks[] = [
                'id' => (string) Str::uuid(),
                'type' => 'heading',
                'level' => 2,
                'text' => "What Does {$focusKeyword} Mean for GTA VI Players?",
            ];
            $blocks[] = [
                'id' => (string) Str::uuid(),
                'type' => 'paragraph',
                'drop_cap' => false,
                'text' => "Evaluating **{$focusKeyword}** alongside confirmed Rockstar Games disclosures helps console owners on PlayStation 5 and Xbox Series X|S plan realistically. Compare active hardware projections and verified milestones on our [release info tracker](/release-info).",
            ];
        }

        $toc = $this->contentBlockService->extractTableOfContents($blocks);
        $hasQuestionHeading = collect($toc)->contains(
            fn (array $h): bool => str_contains($h['text'], '?')
                || preg_match('/^(how|why|what|when|where|can|will|should)\b|\bvs\.?\b/i', $h['text']) === 1
        );

        if (! $hasQuestionHeading || count($toc) < 2) {
            $blocks[] = [
                'id' => (string) Str::uuid(),
                'type' => 'heading',
                'level' => 2,
                'text' => "How Does {$focusKeyword} Impact Launch Planning and Performance?",
            ];
            $blocks[] = [
                'id' => (string) Str::uuid(),
                'type' => 'paragraph',
                'drop_cap' => false,
                'text' => 'From Vice City urban density to NVMe streaming budgets above 5,500 MB/s on PS5 and PS5 Pro, preparation pays off well ahead of launch week. Review interactive benchmarks with the [GTA VI telemetry calculators](/calculators).',
            ];
        }

        return $blocks;
    }

    /**
     * Ensure rich schema blocks, internal links, external citations, GEO telemetry units, and entity grounding.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<int, array{title: string, url: string}>  $groundingSources
     * @return array<int, array<string, mixed>>
     */
    private function ensureStructuredAndGeoSignals(
        array $blocks,
        string $focusKeyword,
        string $mode,
        array $groundingSources,
        string $sourceName,
        string $sourceUrl,
    ): array {
        $hasRichBlock = collect($blocks)->contains(
            fn (array $b): bool => in_array((string) ($b['type'] ?? ''), ['stat_grid', 'spec_table'], true)
        );

        if (! $hasRichBlock) {
            $blocks[] = [
                'id' => (string) Str::uuid(),
                'type' => 'stat_grid',
                'title' => "{$focusKeyword} Telemetry Snapshot",
                'items' => [
                    [
                        'label' => 'Official Console Date',
                        'value' => 'Nov 19, 2026',
                        'note' => 'PlayStation 5 & Xbox Series X|S launch',
                    ],
                    [
                        'label' => 'Projected Install Footprint',
                        'value' => '155–180 GB',
                        'note' => 'Independent NVMe storage projection',
                    ],
                    [
                        'label' => 'Estimated Leonida Area',
                        'value' => '~125 km²',
                        'note' => 'Six confirmed regional zones',
                    ],
                ],
            ];
        }

        $hasQuote = collect($blocks)->contains(fn (array $b): bool => ($b['type'] ?? '') === 'quote');
        if (! $hasQuote && ($mode === 'news' || $groundingSources !== [] || $sourceUrl !== '')) {
            $citeUrl = $groundingSources[0]['url'] ?? ($sourceUrl !== '' && ! str_contains($sourceUrl, 'vertexaisearch.cloud.google.com') ? $sourceUrl : 'https://www.rockstargames.com/VI');
            $citeSource = $groundingSources[0]['title'] ?? ($sourceName !== '' ? $sourceName : 'Rockstar Games Official Disclosure');
            $blocks[] = [
                'id' => (string) Str::uuid(),
                'type' => 'quote',
                'text' => 'Grand Theft Auto VI is officially scheduled to launch for PlayStation 5 and Xbox Series X|S on November 19, 2026, set across the State of Leonida.',
                'source' => $citeSource,
                'cite_url' => $citeUrl,
            ];
        }

        $hasCalculator = collect($blocks)->contains(fn (array $b): bool => ($b['type'] ?? '') === 'calculator_embed');
        $hasMarkdownInternalLink = collect($blocks)->contains(
            fn (array $b): bool => preg_match('/\[[^\]]+\]\(\/[a-z0-9\-\/?=&]*\)/i', (string) ($b['text'] ?? '')) === 1
        );

        if (! $hasCalculator && ! $hasMarkdownInternalLink) {
            $blocks[] = [
                'id' => (string) Str::uuid(),
                'type' => 'calculator_embed',
                'calculator_type' => 'download',
                'title' => 'Calculate Your Pre-Load & SSD Headroom',
                'summary' => 'Test your fiber or cable Mbps download time and console NVMe capacity before launch.',
                'cta_label' => 'Open Download Calculator',
            ];
        } elseif (! $hasMarkdownInternalLink) {
            $blocks[] = [
                'id' => (string) Str::uuid(),
                'type' => 'paragraph',
                'drop_cap' => false,
                'text' => 'For connected reference metrics, inspect the [GTAVI.PRO download sizes hub](/download-sizes), the [Leonida map atlas](/map-atlas), and our [prices and editions matrix](/prices-editions).',
            ];
        }

        $hasFaq = collect($blocks)->contains(fn (array $b): bool => ($b['type'] ?? '') === 'faq');
        if (! $hasFaq) {
            $blocks[] = [
                'id' => (string) Str::uuid(),
                'type' => 'faq',
                'heading' => "{$focusKeyword} Frequently Asked Questions",
                'items' => [
                    [
                        'question' => "When is the official GTA VI release date related to {$focusKeyword}?",
                        'answer' => 'Rockstar Games and Take-Two Interactive confirmed a worldwide console launch date of November 19, 2026 for PlayStation 5 and Xbox Series X|S.',
                    ],
                    [
                        'question' => 'Does GTAVI.PRO separate confirmed announcements from projections?',
                        'answer' => 'Yes. Every GTAVI.PRO dispatch labels Take-Two filings and Rockstar statements separately from technical hardware or pricing estimates.',
                    ],
                ],
            ];
        }

        return $blocks;
    }

    /**
     * Guarantee total word count across rendered blocks stays strictly between 800 and 1,500 words.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    private function enforceWordCountBounds(array $blocks, string $focusKeyword, string $mode, string $title): array
    {
        $metrics = $this->contentBlockService->calculateMetrics($blocks);
        $wordCount = $metrics['word_count'];

        if ($wordCount > self::MAX_WORDS) {
            $blocks = $this->trimExcessParagraphs($blocks);
        }

        $metrics = $this->contentBlockService->calculateMetrics($blocks);
        $wordCount = $metrics['word_count'];

        if ($wordCount < self::MIN_WORDS) {
            $blocks = $this->expandShortArticle($blocks, $focusKeyword, $mode, $title, $wordCount);
        }

        return $blocks;
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    private function trimExcessParagraphs(array $blocks): array
    {
        $firstParagraphSeen = false;
        $protectedIndices = [];
        foreach ($blocks as $idx => $block) {
            if (($block['type'] ?? '') === 'paragraph' && ! $firstParagraphSeen) {
                $firstParagraphSeen = true;
                $protectedIndices[] = $idx;
            }
        }

        for ($i = count($blocks) - 1; $i >= 0; $i--) {
            $currentWords = $this->contentBlockService->calculateMetrics($blocks)['word_count'];
            if ($currentWords <= self::MAX_WORDS) {
                break;
            }

            if (in_array($i, $protectedIndices, true)) {
                continue;
            }

            if (($blocks[$i]['type'] ?? '') === 'paragraph') {
                $text = (string) ($blocks[$i]['text'] ?? '');
                if (preg_match('/\[[^\]]+\]\(\/[a-z0-9\-\/?=&]*\)/i', $text) === 1) {
                    continue;
                }
                unset($blocks[$i]);
            }
        }

        $blocks = array_values($blocks);
        $currentWords = $this->contentBlockService->calculateMetrics($blocks)['word_count'];

        if ($currentWords > self::MAX_WORDS) {
            for ($i = count($blocks) - 1; $i >= 0; $i--) {
                if (($blocks[$i]['type'] ?? '') !== 'paragraph' || $i <= 2) {
                    continue;
                }
                $text = (string) ($blocks[$i]['text'] ?? '');
                $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                while (count($sentences) > 1 && $this->contentBlockService->calculateMetrics($blocks)['word_count'] > self::MAX_WORDS) {
                    array_pop($sentences);
                    $blocks[$i]['text'] = implode(' ', $sentences);
                }
                if ($this->contentBlockService->calculateMetrics($blocks)['word_count'] <= self::MAX_WORDS) {
                    break;
                }
            }
        }

        return array_values($blocks);
    }

    /**
     * Append concise, high-readability editorial context blocks if the LLM fell under 800 words.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    private function expandShortArticle(array $blocks, string $focusKeyword, string $mode, string $title, int $currentWords): array
    {
        $supplementalSections = [
            [
                'heading' => "How {$focusKeyword} Connects to Console Hardware Telemetry",
                'paragraphs' => [
                    "Examining **{$focusKeyword}** alongside current-generation hardware reveals why storage speed and memory bandwidth remain central to Grand Theft Auto VI. PlayStation 5 deploys custom Oodle Kraken decompression above 5,500 MB/s, while Xbox Series X relies on Velocity Architecture and BCPack texture pipelines.",
                    'Both flagship consoles use 16 GB of unified GDDR6 memory, balancing dense Vice City crowd simulation against high-resolution shaders. By reserving at least 195 GB on an internal NVMe SSD, players avoid storefront staging stalls ahead of November 19, 2026.',
                ],
            ],
            [
                'heading' => 'State of Leonida Scale and RAGE 9 Engineering Context',
                'paragraphs' => [
                    'Across the six officially revealed Leonida regions—Vice City, Leonida Keys, Grassrivers, Port Gellhorn, Ambrosia, and Mount Kalaga—asset variety expands sharply over Los Santos. RAGE 9 integrates physically based water reflections, ray-traced global illumination, and dense foliage across roughly 125 km² of projected terrain.',
                    'Tracking verified Take-Two Interactive investor disclosures alongside independent engineering notes keeps expectations grounded. Readers can explore regional benchmarks across our [Leonida map atlas](/map-atlas) or estimate retail tiers using the [prices and editions matrix](/prices-editions).',
                ],
            ],
            [
                'heading' => 'Pre-Launch Preparation Checklist for November 2026',
                'paragraphs' => [
                    'As pre-load windows approach, households running 50 Mbps connections should plan nearly eight hours for a projected 160 GB download. Upgrading to wired Ethernet prevents midnight launch bottlenecks and protects sustained write speeds during patch verification.',
                    sprintf(
                        'Bookmark GTAVI.PRO for ongoing %s coverage separated into confirmed facts and labeled technical projections. Check our [interactive calculators](/calculators) whenever Rockstar Games publishes updated specifications for PlayStation 5, PS5 Pro, or Xbox Series X|S.',
                        $mode === 'news' ? 'news dispatches covering '.$focusKeyword : 'deep-dive reporting on '.$title
                    ),
                ],
            ],
            [
                'heading' => 'Console vs Future PC Outlook and Optimization Notes',
                'paragraphs' => [
                    'Because Grand Theft Auto VI launches first on PlayStation 5 and Xbox Series X|S on November 19, 2026, console players receive tuned frame pacing from day one. PlayStation 5 Pro leverages PlayStation Spectral Super Resolution to sharpen distant neon reflections across Vice Beach without exhausting memory bandwidth.',
                    'Meanwhile, PC hardware enthusiasts tracking future workstation upgrades should monitor 12 GB to 16 GB VRAM budgets alongside PCIe 4.0 DirectStorage support. Compare current tier projections on our [prices and editions matrix](/prices-editions) and follow verified platform announcements via our [release tracker](/release-info).',
                ],
            ],
        ];

        $faqIndex = null;
        foreach ($blocks as $idx => $block) {
            if (($block['type'] ?? '') === 'faq') {
                $faqIndex = $idx;
                break;
            }
        }

        $tailBlocks = $faqIndex !== null ? array_splice($blocks, $faqIndex) : [];

        foreach ($supplementalSections as $section) {
            if ($currentWords >= self::MIN_WORDS + 30) {
                break;
            }

            $blocks[] = [
                'id' => (string) Str::uuid(),
                'type' => 'heading',
                'level' => 2,
                'text' => $section['heading'],
            ];

            foreach ($section['paragraphs'] as $paragraphText) {
                $blocks[] = [
                    'id' => (string) Str::uuid(),
                    'type' => 'paragraph',
                    'drop_cap' => false,
                    'text' => $paragraphText,
                ];

                $currentWords = $this->contentBlockService->calculateMetrics(array_merge($blocks, $tailBlocks))['word_count'];
                if ($currentWords >= self::MIN_WORDS + 30) {
                    break 2;
                }
            }
        }

        $fillerAngles = [
            "Monitoring verified Rockstar Games updates around **{$focusKeyword}** remains critical for console and PC enthusiasts comparing frame rates, asset streaming, and storage ceilings ahead of late 2026. Separating confirmed corporate disclosures from technical modeling prevents misinformation while giving players practical metrics they can test before launch day.",
            'Players optimizing storage on 825 GB PlayStation 5 or 1 TB Xbox Series X consoles should keep at least 20 percent spare NVMe headroom untouched. Sustained flash throughput helps RAGE 9 decompression threads load dense Leonida textures smoothly during high-speed highway chases and dynamic storm transitions.',
        ];

        $safetyCounter = 0;
        while ($currentWords < self::MIN_WORDS && $safetyCounter < 12) {
            $blocks[] = [
                'id' => (string) Str::uuid(),
                'type' => 'paragraph',
                'drop_cap' => false,
                'text' => $fillerAngles[$safetyCounter % count($fillerAngles)],
            ];
            $safetyCounter++;
            $currentWords = $this->contentBlockService->calculateMetrics(array_merge($blocks, $tailBlocks))['word_count'];
        }

        return array_values(array_merge($blocks, $tailBlocks));
    }

    /**
     * Calculate exact token consumption and estimated USD API cost from Gemini usageMetadata.
     *
     * @param  array<string, mixed>  $responseData
     * @return array{
     *     ai_model: string,
     *     ai_prompt_tokens: int,
     *     ai_completion_tokens: int,
     *     ai_thinking_tokens: int,
     *     ai_search_queries: int,
     *     ai_estimated_cost_usd: float
     * }
     */
    public function calculateUsageAndCost(array $responseData, string $prompt, bool $searchEnabled): array
    {
        $model = trim((string) data_get($responseData, 'modelVersion', (string) config('services.gemini.model', 'gemini-flash-latest')));
        if ($model === '') {
            $model = 'gemini-flash-latest';
        }

        $usage = data_get($responseData, 'usageMetadata', []);
        $promptTokens = is_array($usage) ? (int) ($usage['promptTokenCount'] ?? 0) : 0;
        $completionTokens = is_array($usage) ? (int) ($usage['candidatesTokenCount'] ?? 0) : 0;
        $thinkingTokens = is_array($usage) ? (int) ($usage['thoughtsTokenCount'] ?? 0) : 0;

        if ($promptTokens === 0 && $prompt !== '') {
            $promptTokens = max(1, (int) ceil(mb_strlen($prompt) / 4));
        }

        if ($completionTokens === 0) {
            $rawText = (string) data_get($responseData, 'candidates.0.content.parts.0.text', '');
            if ($rawText !== '') {
                $completionTokens = max(1, (int) ceil(mb_strlen($rawText) / 4));
            }
        }

        $webSearchQueries = data_get($responseData, 'candidates.0.groundingMetadata.webSearchQueries', []);
        $groundingChunks = data_get($responseData, 'candidates.0.groundingMetadata.groundingChunks', []);

        if (is_array($webSearchQueries) && $webSearchQueries !== []) {
            $searchQueries = count($webSearchQueries);
        } elseif ($searchEnabled && is_array($groundingChunks) && $groundingChunks !== []) {
            $searchQueries = 1;
        } else {
            $searchQueries = 0;
        }

        $inputRate = (float) config('services.gemini.pricing.input_per_million', 0.30);
        $outputRate = (float) config('services.gemini.pricing.output_per_million', 2.50);
        $searchRate = (float) config('services.gemini.pricing.search_grounding_per_request', 0.035);

        $tokenCost = (($promptTokens / 1_000_000) * $inputRate)
            + ((($completionTokens + $thinkingTokens) / 1_000_000) * $outputRate);
        $searchCost = $searchQueries > 0 ? $searchRate : 0.0;

        return [
            'ai_model' => Str::limit($model, 64, ''),
            'ai_prompt_tokens' => $promptTokens,
            'ai_completion_tokens' => $completionTokens,
            'ai_thinking_tokens' => $thinkingTokens,
            'ai_search_queries' => $searchQueries,
            'ai_estimated_cost_usd' => round($tokenCost + $searchCost, 6),
        ];
    }
}
