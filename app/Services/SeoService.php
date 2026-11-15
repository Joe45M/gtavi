<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Str;

final readonly class SeoService
{
    public function __construct(
        private ContentBlockService $contentBlockService,
    ) {}

    /**
     * Compute WordPress Yoast/RankMath-grade SEO score, AI GEO readiness score, readability score, and itemized checklist.
     *
     * @param  array<string, mixed>  $data
     * @return array{
     *     seo_score: int,
     *     geo_score: int,
     *     readability_score: int,
     *     word_count: int,
     *     reading_time_minutes: int,
     *     checks: array<int, array{id: string, label: string, status: string, detail: string, points: int, max_points: int}>,
     *     geo_checks: array<int, array{id: string, label: string, status: string, detail: string, points: int, max_points: int}>,
     *     readability_checks: array<int, array{id: string, label: string, status: string, detail: string}>
     * }
     */
    public function analyzeContent(array $data): array
    {
        $title = trim((string) ($data['title'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));
        $metaTitle = trim((string) ($data['meta_title'] ?? '')) ?: $title;
        $metaDesc = trim((string) ($data['meta_description'] ?? '')) ?: trim((string) ($data['excerpt'] ?? ''));
        $keyword = mb_strtolower(trim((string) ($data['focus_keyword'] ?? '')));
        $blocks = is_array($data['content_blocks'] ?? null) ? $data['content_blocks'] : [];

        $metrics = $this->contentBlockService->calculateMetrics($blocks);
        $plainText = $this->contentBlockService->extractPlainText($blocks);
        $toc = $this->contentBlockService->extractTableOfContents($blocks);
        $faqs = $this->contentBlockService->extractFaqItems($blocks);

        $firstParagraph = $this->extractFirstParagraph($blocks);
        $hasInternalLink = $this->hasInternalLink($blocks);
        $hasStructuredBlock = $this->hasRichStructuredBlock($blocks);

        $checks = [];

        $checks[] = $this->makeCheck(
            'focus-keyword',
            'Focus Keyword Configured',
            $keyword !== '' ? 'pass' : 'fail',
            $keyword !== '' ? "Targeting '{$keyword}'." : 'Set a primary focus keyword to evaluate semantic alignment.',
            $keyword !== '' ? 10 : 0,
            10
        );

        $kwInTitle = $keyword !== '' && str_contains(mb_strtolower($title), $keyword);
        $checks[] = $this->makeCheck(
            'kw-title',
            'Focus Keyword in H1 Title',
            $kwInTitle ? 'pass' : ($keyword === '' ? 'warn' : 'fail'),
            $kwInTitle ? 'Primary keyword appears in the main H1 headline.' : 'Include the exact focus keyword in the article headline.',
            $kwInTitle ? 12 : 0,
            12
        );

        $kwSlug = $keyword !== '' ? Str::slug($keyword) : '';
        $kwInSlug = $kwSlug !== '' && str_contains(mb_strtolower($slug), $kwSlug);
        $checks[] = $this->makeCheck(
            'kw-slug',
            'Focus Keyword in URL Slug',
            $kwInSlug ? 'pass' : ($keyword === '' ? 'warn' : 'fail'),
            $kwInSlug ? 'Clean canonical slug contains target keyword.' : 'Update URL slug to reflect the focus keyword.',
            $kwInSlug ? 10 : 0,
            10
        );

        $kwInMetaDesc = $keyword !== '' && str_contains(mb_strtolower($metaDesc), $keyword);
        $checks[] = $this->makeCheck(
            'kw-meta-desc',
            'Focus Keyword in Meta Description',
            $kwInMetaDesc ? 'pass' : 'warn',
            $kwInMetaDesc ? 'SERP snippet highlights the focus keyword.' : 'Weave the focus keyword naturally into the meta description.',
            $kwInMetaDesc ? 10 : 0,
            10
        );

        $kwInIntro = $keyword !== '' && str_contains(mb_strtolower($firstParagraph), $keyword);
        $checks[] = $this->makeCheck(
            'kw-intro',
            'Focus Keyword in Opening Paragraph',
            $kwInIntro ? 'pass' : 'warn',
            $kwInIntro ? 'Opening hook confirms topical relevance immediately.' : 'Mention the focus keyword in the first paragraph.',
            $kwInIntro ? 10 : 0,
            10
        );

        $kwInSubheadings = $keyword !== '' && collect($toc)->contains(
            fn (array $item): bool => str_contains(mb_strtolower($item['text']), $keyword)
        );
        $checks[] = $this->makeCheck(
            'kw-subheadings',
            'Focus Keyword in Subheadings',
            $kwInSubheadings ? 'pass' : 'warn',
            $kwInSubheadings ? 'At least one H2/H3 section reinforces the topic.' : 'Add an H2 or H3 subheading referencing your focus keyword.',
            $kwInSubheadings ? 8 : 0,
            8
        );

        $metaTitleLen = mb_strlen($metaTitle);
        $titleOptimal = $metaTitleLen >= 35 && $metaTitleLen <= 68;
        $checks[] = $this->makeCheck(
            'meta-title-length',
            'SEO Meta Title Length (35–68 chars)',
            $titleOptimal ? 'pass' : 'warn',
            "Current length: {$metaTitleLen} characters.",
            $titleOptimal ? 10 : ($metaTitleLen > 0 ? 5 : 0),
            10
        );

        $metaDescLen = mb_strlen($metaDesc);
        $descOptimal = $metaDescLen >= 115 && $metaDescLen <= 165;
        $checks[] = $this->makeCheck(
            'meta-desc-length',
            'Meta Description Length (115–165 chars)',
            $descOptimal ? 'pass' : 'warn',
            "Current length: {$metaDescLen} characters.",
            $descOptimal ? 10 : ($metaDescLen >= 60 ? 5 : 0),
            10
        );

        $wordCount = $metrics['word_count'];
        $wordStatus = $wordCount >= 550 ? 'pass' : ($wordCount >= 250 ? 'warn' : 'fail');
        $wordPoints = $wordCount >= 550 ? 10 : ($wordCount >= 250 ? 6 : 2);
        $checks[] = $this->makeCheck(
            'word-count',
            'Long-Form Authority Depth (550+ words)',
            $wordStatus,
            "Current length: {$wordCount} words (~{$metrics['reading_time_minutes']} min read).",
            $wordPoints,
            10
        );

        $richScore = ($hasStructuredBlock ? 6 : 0) + ($hasInternalLink ? 4 : 0);
        $checks[] = $this->makeCheck(
            'rich-blocks',
            'Rich Schema Blocks & Internal Links',
            $richScore === 10 ? 'pass' : ($richScore > 0 ? 'warn' : 'fail'),
            sprintf(
                'Tables/FAQs/Stats: %s | Internal links: %s%s',
                $hasStructuredBlock ? 'Yes' : 'Missing',
                $hasInternalLink ? 'Yes' : 'Missing',
                $faqs !== [] ? ' ('.count($faqs).' FAQ schema items)' : ''
            ),
            $richScore,
            10
        );

        $geoChecks = $this->evaluateGeoChecks($plainText, $toc, $faqs);
        $checks = array_merge($checks, $geoChecks);

        $totalPoints = array_sum(array_column($checks, 'points'));
        $maxPoints = max(1, array_sum(array_column($checks, 'max_points')));
        $seoScore = min(100, (int) round(($totalPoints / $maxPoints) * 100));

        $geoPoints = array_sum(array_column($geoChecks, 'points'));
        $geoMaxPoints = max(1, array_sum(array_column($geoChecks, 'max_points')));
        $geoScore = min(100, (int) round(($geoPoints / $geoMaxPoints) * 100));

        $readability = $this->analyzeReadability($plainText, $blocks, $toc);

        return [
            'seo_score' => $seoScore,
            'geo_score' => $geoScore,
            'readability_score' => $readability['score'],
            'word_count' => $wordCount,
            'reading_time_minutes' => $metrics['reading_time_minutes'],
            'checks' => $checks,
            'geo_checks' => $geoChecks,
            'readability_checks' => $readability['checks'],
        ];
    }

    /**
     * Build unified SEO meta and multi-entity @graph JSON-LD payload for public routes.
     *
     * @param  array<int, array{name: string, url: string}>  $breadcrumbs
     * @param  array<int, array{question: string, answer: string}>  $faqItems
     * @param  array<int, array<string, mixed>>  $extraGraphNodes
     * @return array<string, mixed>
     */
    public function buildPageMeta(
        string $title,
        string $description,
        string $canonicalUrl,
        string $ogType = 'website',
        array $breadcrumbs = [],
        array $faqItems = [],
        ?Post $post = null,
        string $robots = 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
        ?string $ogImage = null,
        ?string $markdownUrl = null,
        array $extraGraphNodes = [],
    ): array {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');
        $disclaimer = 'GTAVI.PRO is an independent fan and technical telemetry site not affiliated with Rockstar Games or Take-Two Interactive Software, Inc.';
        $resolvedDescription = Str::limit(trim($description), 165, '');
        $resolvedOgImage = $ogImage ?: ($post !== null ? $siteUrl.'/og/'.$post->slug.'.svg' : $siteUrl.'/og/default.svg');
        $resolvedOgAlt = $post?->featured_image_label ?: $title;

        $graph = [
            [
                '@type' => 'WebSite',
                '@id' => $siteUrl.'/#website',
                'url' => $siteUrl.'/',
                'name' => 'GTAVI.PRO',
                'description' => 'Independent GTA VI release tracker, download calculator, edition pricing matrix, and Leonida technical analysis.',
                'inLanguage' => 'en-US',
                'publisher' => [
                    '@id' => $siteUrl.'/#organization',
                ],
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => $siteUrl.'/gta?q={search_term_string}',
                    ],
                    'query-input' => 'required name=search_term_string',
                ],
            ],
            [
                '@type' => 'Organization',
                '@id' => $siteUrl.'/#organization',
                'name' => 'GTAVI.PRO Independent Telemetry Desk',
                'url' => $siteUrl.'/',
                'description' => $disclaimer,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $siteUrl.'/og/default.svg',
                    'width' => 1200,
                    'height' => 630,
                ],
            ],
            [
                '@type' => 'VideoGame',
                '@id' => $siteUrl.'/#gtavi-entity',
                'name' => 'Grand Theft Auto VI',
                'alternateName' => ['GTA VI', 'GTA 6'],
                'applicationCategory' => 'Game',
                'operatingSystem' => ['PlayStation 5', 'Xbox Series X|S'],
                'datePublished' => '2026-11-19',
                'sameAs' => [
                    'https://www.wikidata.org/wiki/Q23647070',
                    'https://en.wikipedia.org/wiki/Grand_Theft_Auto_VI',
                    'https://www.rockstargames.com/VI',
                ],
            ],
            [
                '@type' => 'WebPage',
                '@id' => $canonicalUrl.'#webpage',
                'url' => $canonicalUrl,
                'name' => $title,
                'description' => $resolvedDescription,
                'isPartOf' => [
                    '@id' => $siteUrl.'/#website',
                ],
                'about' => [
                    '@id' => $siteUrl.'/#gtavi-entity',
                ],
                'inLanguage' => 'en-US',
                'speakable' => [
                    '@type' => 'SpeakableSpecification',
                    'cssSelector' => ['#main-heading', '.article-lead-summary'],
                ],
            ],
        ];

        if ($breadcrumbs !== []) {
            $itemList = [];
            foreach ($breadcrumbs as $idx => $crumb) {
                $itemList[] = [
                    '@type' => 'ListItem',
                    'position' => $idx + 1,
                    'name' => $crumb['name'],
                    'item' => $crumb['url'],
                ];
            }
            $graph[] = [
                '@type' => 'BreadcrumbList',
                'itemListElement' => $itemList,
            ];
        }

        if ($post !== null) {
            $keywords = array_values(array_unique(array_filter(array_merge(
                [$post->focus_keyword],
                $post->relationLoaded('tags') ? $post->tags->pluck('name')->all() : []
            ))));

            $articleNode = [
                '@type' => $post->schema_type ?: 'BlogPosting',
                '@id' => $canonicalUrl.'#article',
                'headline' => $post->meta_title ?: $post->title,
                'description' => $post->meta_description ?: $post->excerpt,
                'datePublished' => ($post->published_at ?? $post->created_at)?->toIso8601String(),
                'dateModified' => ($post->updated_at ?? $post->published_at ?? $post->created_at)?->toIso8601String(),
                'wordCount' => $post->word_count,
                'inLanguage' => 'en-US',
                'mainEntityOfPage' => [
                    '@id' => $canonicalUrl.'#webpage',
                ],
                'about' => [
                    '@id' => $siteUrl.'/#gtavi-entity',
                ],
                'articleSection' => $post->relationLoaded('category') && $post->category !== null
                    ? $post->category->name
                    : 'GTA VI Technical Analysis',
                'image' => [
                    '@type' => 'ImageObject',
                    'url' => $resolvedOgImage,
                    'width' => 1200,
                    'height' => 630,
                    'caption' => $resolvedOgAlt,
                ],
                'author' => [
                    '@type' => 'Person',
                    'name' => $post->author_name,
                    'jobTitle' => $post->author_role,
                    'worksFor' => [
                        '@id' => $siteUrl.'/#organization',
                    ],
                ],
                'publisher' => [
                    '@id' => $siteUrl.'/#organization',
                ],
                'isAccessibleForFree' => true,
            ];

            if ($keywords !== []) {
                $articleNode['keywords'] = $keywords;
            }

            $citations = $this->contentBlockService->extractCitations($post->content_blocks ?? []);
            if ($citations !== []) {
                $articleNode['citation'] = $citations;
            }

            $graph[] = $articleNode;
        }

        if ($faqItems !== []) {
            $entities = [];
            foreach ($faqItems as $item) {
                $entities[] = [
                    '@type' => 'Question',
                    'name' => $item['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $item['answer'],
                    ],
                ];
            }
            $graph[] = [
                '@type' => 'FAQPage',
                'mainEntity' => $entities,
            ];
        }

        foreach ($extraGraphNodes as $extraNode) {
            if (is_array($extraNode) && $extraNode !== []) {
                $graph[] = $extraNode;
            }
        }

        return [
            'title' => $title,
            'description' => $resolvedDescription,
            'canonical' => $canonicalUrl,
            'og_type' => $ogType,
            'og_title' => $post?->og_title ?: $title,
            'og_description' => Str::limit($post?->og_description ?: $description, 165, ''),
            'og_image' => $resolvedOgImage,
            'og_image_width' => 1200,
            'og_image_height' => 630,
            'og_image_alt' => $resolvedOgAlt,
            'markdown_url' => $markdownUrl,
            'robots' => $robots,
            'json_ld' => [
                '@context' => 'https://schema.org',
                '@graph' => $graph,
            ],
        ];
    }

    /**
     * Evaluate AI SEO / Generative Engine Optimization (GEO & AEO) retrieval readiness.
     *
     * @param  array<int, array{id: string, level: int, text: string}>  $toc
     * @param  array<int, array{question: string, answer: string}>  $faqs
     * @return array<int, array{id: string, label: string, status: string, detail: string, points: int, max_points: int}>
     */
    private function evaluateGeoChecks(string $plainText, array $toc, array $faqs): array
    {
        $geoChecks = [];

        $hasQuestionHeading = $faqs !== [] || collect($toc)->contains(function (array $item): bool {
            $heading = mb_strtolower(trim($item['text']));

            return str_contains($heading, '?')
                || preg_match('/^(how|why|what|when|where|can|will|should)\b|\bvs\.?\b/i', $heading) === 1;
        });
        $geoChecks[] = $this->makeCheck(
            'geo-answer-headings',
            'AEO Answer-Ready Headings & FAQs',
            $hasQuestionHeading ? 'pass' : 'warn',
            $hasQuestionHeading
                ? 'Headings/FAQs match conversational AI retrieval prompts.'
                : 'Add a question/comparison H2/H3 (How, Why, vs) or FAQ block for AI Overviews.',
            $hasQuestionHeading ? 8 : 0,
            8
        );

        $unitMatches = preg_match_all(
            '/\b\d+(?:\.\d+)?\s*(?:gb|mb\/s|mbps|tb|km²|km2|fps|ms|usd|gbp|eur|cad|aud|jpy|202\d)\b|\d+(?:\.\d+)?%|\$\d+/iu',
            $plainText
        );
        $hasTelemetryDensity = is_int($unitMatches) && $unitMatches >= 1;
        $geoChecks[] = $this->makeCheck(
            'geo-telemetry-density',
            'GEO Quantifiable Telemetry & Units',
            $hasTelemetryDensity ? 'pass' : 'warn',
            $hasTelemetryDensity
                ? sprintf('Detected %d concrete unit/metric data point(s) boosting information gain.', (int) $unitMatches)
                : 'Include specific metrics (GB, MB/s, km², FPS, $, %) to boost LLM citation likelihood.',
            $hasTelemetryDensity ? 8 : 0,
            8
        );

        $lowerText = mb_strtolower($plainText);
        $entities = ['gta', 'grand theft auto', 'leonida', 'vice city', 'rockstar', 'take-two', 'ps5', 'playstation', 'xbox', 'rage', 'nvme', 'ssd'];
        $matchedEntities = array_values(array_filter(
            $entities,
            static fn (string $entity): bool => str_contains($lowerText, $entity)
        ));
        $entityCount = count($matchedEntities);
        $hasEntityGrounding = $entityCount >= 2;
        $geoChecks[] = $this->makeCheck(
            'geo-entity-grounding',
            'GEO Entity Co-Occurrence & Grounding',
            $hasEntityGrounding ? 'pass' : 'warn',
            $hasEntityGrounding
                ? sprintf('Grounded across %d domain entities (%s).', $entityCount, implode(', ', array_slice($matchedEntities, 0, 4)))
                : 'Reference at least 2 core domain entities (e.g., Leonida, Rockstar, PS5, RAGE, SSD).',
            $hasEntityGrounding ? 8 : ($entityCount === 1 ? 4 : 0),
            8
        );

        return $geoChecks;
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    private function extractFirstParagraph(array $blocks): string
    {
        foreach ($blocks as $block) {
            if (($block['type'] ?? '') === 'paragraph') {
                return (string) ($block['text'] ?? '');
            }
        }

        return '';
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    private function hasInternalLink(array $blocks): bool
    {
        foreach ($blocks as $block) {
            $type = (string) ($block['type'] ?? '');
            if ($type === 'calculator_embed') {
                return true;
            }
            $text = (string) ($block['text'] ?? '');
            if (preg_match('/\[[^\]]+\]\(\/[a-z0-9\-\/?=&]*\)/i', $text) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    private function hasRichStructuredBlock(array $blocks): bool
    {
        foreach ($blocks as $block) {
            if (in_array((string) ($block['type'] ?? ''), ['spec_table', 'faq', 'stat_grid', 'calculator_embed'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<int, array{id: string, level: int, text: string}>  $toc
     * @return array{score: int, checks: array<int, array{id: string, label: string, status: string, detail: string}>}
     */
    private function analyzeReadability(string $plainText, array $blocks, array $toc): array
    {
        $sentences = preg_split('/[.!?]+(?:\s|$)/', $plainText, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $sentenceCount = max(1, count($sentences));
        $wordCount = str_word_count($plainText);
        $avgSentenceWords = $wordCount > 0 ? round($wordCount / $sentenceCount, 1) : 0.0;

        $longParagraphs = 0;
        $paragraphCount = 0;
        foreach ($blocks as $block) {
            if (($block['type'] ?? '') === 'paragraph') {
                $paragraphCount++;
                if (str_word_count((string) ($block['text'] ?? '')) > 110) {
                    $longParagraphs++;
                }
            }
        }

        $score = 100;
        $checks = [];

        $sentenceOk = $avgSentenceWords > 0 && $avgSentenceWords <= 23.0;
        if (! $sentenceOk) {
            $score -= 20;
        }
        $checks[] = [
            'id' => 'sentence-length',
            'label' => 'Average Sentence Length',
            'status' => $sentenceOk ? 'pass' : 'warn',
            'detail' => "Average {$avgSentenceWords} words per sentence (target <= 23 words).",
        ];

        $subheadingOk = count($toc) >= 2;
        if (! $subheadingOk) {
            $score -= 25;
        }
        $checks[] = [
            'id' => 'subheading-rhythm',
            'label' => 'Section Scannability (H2/H3)',
            'status' => $subheadingOk ? 'pass' : 'warn',
            'detail' => count($toc).' section headings detected for TOC navigation.',
        ];

        $paragraphOk = $paragraphCount > 0 && $longParagraphs === 0;
        if (! $paragraphOk) {
            $score -= 15;
        }
        $checks[] = [
            'id' => 'paragraph-length',
            'label' => 'Concise Paragraph Density',
            'status' => $paragraphOk ? 'pass' : 'warn',
            'detail' => $longParagraphs === 0 ? 'All paragraphs stay crisp and scannable.' : "{$longParagraphs} paragraph(s) exceed 110 words.",
        ];

        return [
            'score' => max(20, $score),
            'checks' => $checks,
        ];
    }

    /**
     * @return array{id: string, label: string, status: string, detail: string, points: int, max_points: int}
     */
    private function makeCheck(
        string $id,
        string $label,
        string $status,
        string $detail,
        int $points,
        int $maxPoints
    ): array {
        return [
            'id' => $id,
            'label' => $label,
            'status' => $status,
            'detail' => $detail,
            'points' => $points,
            'max_points' => $maxPoints,
        ];
    }
}
