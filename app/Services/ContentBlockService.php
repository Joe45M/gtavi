<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

final readonly class ContentBlockService
{
    /**
     * Render structured WordPress-style content blocks into sanitized, accessible HTML.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     */
    public function renderBlocks(array $blocks): string
    {
        $chunks = [];

        foreach ($blocks as $index => $block) {
            if (! is_array($block)) {
                continue;
            }

            $type = (string) ($block['type'] ?? 'paragraph');
            $rendered = match ($type) {
                'heading' => $this->renderHeading($block),
                'paragraph' => $this->renderParagraph($block),
                'callout' => $this->renderCallout($block),
                'spec_table' => $this->renderSpecTable($block, $index),
                'faq' => $this->renderFaq($block, $index),
                'calculator_embed' => $this->renderCalculatorEmbed($block),
                'quote' => $this->renderQuote($block),
                'code' => $this->renderCode($block, $index),
                'stat_grid' => $this->renderStatGrid($block),
                default => $this->renderParagraph($block),
            };

            if ($rendered !== '') {
                $chunks[] = $rendered;
            }
        }

        return implode("\n\n", $chunks);
    }

    /**
     * Extract a sequential Table of Contents from heading blocks.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array{id: string, level: int, text: string}>
     */
    public function extractTableOfContents(array $blocks): array
    {
        $toc = [];

        foreach ($blocks as $block) {
            if (($block['type'] ?? '') !== 'heading') {
                continue;
            }

            $text = trim((string) ($block['text'] ?? ''));
            if ($text === '') {
                continue;
            }

            $level = (int) ($block['level'] ?? 2);
            $level = $level === 3 ? 3 : 2;

            $toc[] = [
                'id' => Str::slug($text),
                'level' => $level,
                'text' => $text,
            ];
        }

        return $toc;
    }

    /**
     * Extract flattened FAQ pairs for JSON-LD FAQPage schema generation.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array{question: string, answer: string}>
     */
    public function extractFaqItems(array $blocks): array
    {
        $items = [];

        foreach ($blocks as $block) {
            if (($block['type'] ?? '') !== 'faq') {
                continue;
            }

            $blockItems = $block['items'] ?? [];
            if (! is_array($blockItems)) {
                continue;
            }

            foreach ($blockItems as $item) {
                $q = trim((string) ($item['question'] ?? ''));
                $a = trim((string) ($item['answer'] ?? ''));
                if ($q !== '' && $a !== '') {
                    $items[] = [
                        'question' => $q,
                        'answer' => $a,
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * Extract plain text across all blocks for word count and SEO keyword scanning.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     */
    public function extractPlainText(array $blocks): string
    {
        $parts = [];

        foreach ($blocks as $block) {
            $type = (string) ($block['type'] ?? '');
            $parts[] = match ($type) {
                'heading', 'paragraph' => (string) ($block['text'] ?? ''),
                'callout' => trim(((string) ($block['title'] ?? '')).' '.((string) ($block['text'] ?? ''))),
                'quote' => trim(((string) ($block['text'] ?? '')).' '.((string) ($block['source'] ?? ''))),
                'calculator_embed' => trim(((string) ($block['title'] ?? '')).' '.((string) ($block['summary'] ?? ''))),
                'code' => trim(((string) ($block['caption'] ?? '')).' '.((string) ($block['code'] ?? ''))),
                'spec_table' => $this->flattenTableText($block),
                'faq' => $this->flattenFaqText($block),
                'stat_grid' => $this->flattenStatGridText($block),
                default => '',
            };
        }

        return trim(preg_replace('/\s+/', ' ', implode(' ', $parts)) ?? '');
    }

    /**
     * Calculate word count and estimated reading time in minutes.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array{word_count: int, reading_time_minutes: int}
     */
    public function calculateMetrics(array $blocks): array
    {
        $plainText = $this->extractPlainText($blocks);
        $stripped = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $plainText) ?? $plainText;
        $words = str_word_count(strip_tags($stripped));
        $readingTime = max(1, (int) ceil($words / 215));

        return [
            'word_count' => $words,
            'reading_time_minutes' => $readingTime,
        ];
    }

    /**
     * Provide clean default payloads when inserting a block inside Studio.
     *
     * @return array<string, mixed>
     */
    public function defaultBlockTemplate(string $type): array
    {
        return match ($type) {
            'heading' => [
                'id' => (string) Str::uuid(),
                'type' => 'heading',
                'level' => 2,
                'text' => 'New Technical Section Heading',
            ],
            'callout' => [
                'id' => (string) Str::uuid(),
                'type' => 'callout',
                'tone' => 'info',
                'title' => 'Editorial Verification Note',
                'text' => 'Distinguish confirmed Rockstar Games / Take-Two announcements from independent technical projections.',
            ],
            'spec_table' => [
                'id' => (string) Str::uuid(),
                'type' => 'spec_table',
                'caption' => 'Platform Comparison Matrix',
                'headers' => ['Platform', 'Target Output', 'Estimated Size'],
                'rows' => [
                    ['PlayStation 5 Pro', '4K PSSR / 60 FPS Target', '165 GB'],
                    ['Xbox Series X', 'Dynamic 4K / 30-60 FPS', '155 GB'],
                ],
            ],
            'faq' => [
                'id' => (string) Str::uuid(),
                'type' => 'faq',
                'heading' => 'Frequently Asked Questions',
                'items' => [
                    [
                        'question' => 'Is GTAVI.PRO affiliated with Rockstar Games?',
                        'answer' => 'No. GTAVI.PRO is an independent fan and technical telemetry publication not affiliated with Rockstar Games or Take-Two Interactive.',
                    ],
                ],
            ],
            'calculator_embed' => [
                'id' => (string) Str::uuid(),
                'type' => 'calculator_embed',
                'calculator_type' => 'download',
                'title' => 'Calculate Your Exact GTA VI Pre-Load Window',
                'summary' => 'Model fiber, cable, or 5G download duration against a 155 GB–185 GB launch package.',
                'cta_label' => 'Launch Download Calculator',
            ],
            'quote' => [
                'id' => (string) Str::uuid(),
                'type' => 'quote',
                'text' => 'Grand Theft Auto VI heads to the state of Leonida, home to the neon-soaked streets of Vice City and beyond.',
                'source' => 'Official Trailer 1 Press Release',
                'cite_url' => 'https://gtavi.pro/release-info',
            ],
            'code' => [
                'id' => (string) Str::uuid(),
                'type' => 'code',
                'caption' => 'JSON-LD Structured Telemetry Snippet',
                'language' => 'json',
                'code' => "{\n  \"project\": \"gtavi.pro\",\n  \"engine\": \"RAGE 9 (Projected)\",\n  \"affiliation\": \"None - Independent Fan Resource\"\n}",
            ],
            'stat_grid' => [
                'id' => (string) Str::uuid(),
                'type' => 'stat_grid',
                'title' => 'Key Telemetry Benchmarks',
                'items' => [
                    ['label' => 'Projected Base Size', 'value' => '155 GB', 'note' => 'Console launch baseline'],
                    ['label' => 'Target Standard Price', 'value' => '$69.99', 'note' => 'Industry AAA tier'],
                    ['label' => 'Projected Map Area', 'value' => '~125 km²', 'note' => 'State of Leonida'],
                ],
            ],
            default => [
                'id' => (string) Str::uuid(),
                'type' => 'paragraph',
                'drop_cap' => false,
                'text' => 'Write clear, high-authority analysis here. Supports **bold**, *italic*, `inline code`, and [internal links](/calculators).',
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private function renderHeading(array $block): string
    {
        $text = trim((string) ($block['text'] ?? ''));
        if ($text === '') {
            return '';
        }

        $level = (int) ($block['level'] ?? 2);
        $tag = $level === 3 ? 'h3' : 'h2';
        $id = e(Str::slug($text));

        return sprintf(
            '<%1$s id="%2$s" class="article-heading article-heading-%1$s">%3$s</%1$s>',
            $tag,
            $id,
            e($text)
        );
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private function renderParagraph(array $block): string
    {
        $text = trim((string) ($block['text'] ?? ''));
        if ($text === '') {
            return '';
        }

        $class = ($block['drop_cap'] ?? false) ? 'article-paragraph article-drop-cap' : 'article-paragraph';

        return '<p class="'.$class.'">'.$this->formatInline($text).'</p>';
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private function renderCallout(array $block): string
    {
        $tone = (string) ($block['tone'] ?? 'info');
        $allowedTones = ['info', 'warning', 'confirmed', 'disclaimer'];
        if (! in_array($tone, $allowedTones, true)) {
            $tone = 'info';
        }

        $title = trim((string) ($block['title'] ?? 'Editorial Note'));
        $text = trim((string) ($block['text'] ?? ''));

        if ($title === '' && $text === '') {
            return '';
        }

        return sprintf(
            '<aside class="article-callout article-callout-%s" aria-label="%s"><p class="callout-title">%s</p><p class="callout-body">%s</p></aside>',
            e($tone),
            e($title),
            e($title),
            $this->formatInline($text)
        );
    }

    /**
     * Render responsive data table complying with modern-web-guidance `responsive-table`.
     *
     * @param  array<string, mixed>  $block
     */
    private function renderSpecTable(array $block, int $index): string
    {
        $caption = trim((string) ($block['caption'] ?? 'Technical Specification Table'));
        $headers = is_array($block['headers'] ?? null) ? array_values($block['headers']) : [];
        $rows = is_array($block['rows'] ?? null) ? array_values($block['rows']) : [];

        if ($headers === [] || $rows === []) {
            return '';
        }

        $captionId = 'spec-table-caption-'.$index.'-'.Str::slug($caption);
        $cssVars = [];
        $headerHtml = '';

        foreach ($headers as $colIndex => $header) {
            $labelText = trim((string) $header);
            $safeCssLabel = str_replace(['"', '\\', "\n", "\r"], '', $labelText);
            $cssVars[] = sprintf('--label-%d: "%s"', $colIndex + 1, $safeCssLabel);
            $headerHtml .= '<th scope="col">'.e($labelText).'</th>';
        }

        $bodyHtml = '';
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $cells = array_values($row);
            $rowHtml = '';
            foreach ($headers as $colIndex => $header) {
                $cellValue = (string) ($cells[$colIndex] ?? '—');
                if ($colIndex === 0) {
                    $rowHtml .= '<th scope="row">'.e($cellValue).'</th>';
                } else {
                    $rowHtml .= '<td>'.$this->formatInline($cellValue).'</td>';
                }
            }
            $bodyHtml .= '<tr>'.$rowHtml.'</tr>';
        }

        $styleAttr = implode('; ', $cssVars);

        return sprintf(
            '<div tabindex="0" role="region" aria-labelledby="%s" class="table-wrapper">'
            .'<table class="vice-table" style="%s">'
            .'<caption id="%s">%s</caption>'
            .'<thead><tr>%s</tr></thead>'
            .'<tbody>%s</tbody>'
            .'</table>'
            .'</div>',
            e($captionId),
            e($styleAttr),
            e($captionId),
            e($caption),
            $headerHtml,
            $bodyHtml
        );
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private function renderFaq(array $block, int $index): string
    {
        $heading = trim((string) ($block['heading'] ?? 'Frequently Asked Questions'));
        $items = is_array($block['items'] ?? null) ? $block['items'] : [];
        if ($items === []) {
            return '';
        }

        $groupName = 'post-faq-'.$index;
        $detailsHtml = '';

        foreach ($items as $item) {
            $q = trim((string) ($item['question'] ?? ''));
            $a = trim((string) ($item['answer'] ?? ''));
            if ($q === '' || $a === '') {
                continue;
            }

            $detailsHtml .= sprintf(
                '<details name="%s" class="vice-faq-item"><summary class="vice-faq-summary">%s</summary><div class="vice-faq-answer"><p>%s</p></div></details>',
                e($groupName),
                e($q),
                $this->formatInline($a)
            );
        }

        if ($detailsHtml === '') {
            return '';
        }

        return sprintf(
            '<section class="vice-faq-section" aria-label="%s"><h2 id="%s" class="article-heading article-heading-h2">%s</h2><div class="vice-faq-list">%s</div></section>',
            e($heading),
            e(Str::slug($heading)),
            e($heading),
            $detailsHtml
        );
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private function renderCalculatorEmbed(array $block): string
    {
        $calcType = (string) ($block['calculator_type'] ?? 'download');
        $tab = match ($calcType) {
            'pc' => 'pc',
            'edition' => 'edition',
            'map' => 'map',
            default => 'download',
        };

        $title = trim((string) ($block['title'] ?? 'Interactive Telemetry Calculator'));
        $summary = trim((string) ($block['summary'] ?? 'Run live GTA VI download, PC hardware, edition currency, or Leonida map math.'));
        $ctaLabel = trim((string) ($block['cta_label'] ?? 'Open Interactive Calculator'));
        $url = '/calculators?tab='.$tab;

        return sprintf(
            '<aside class="article-calc-embed" aria-label="%s">'
            .'<div class="calc-embed-meta"><span class="calc-embed-kicker">INTERACTIVE TOOLKIT</span><p class="calc-embed-title">%s</p><p class="calc-embed-summary">%s</p></div>'
            .'<a href="%s" class="calc-embed-cta">%s</a>'
            .'</aside>',
            e($title),
            e($title),
            $this->formatInline($summary),
            e($url),
            e($ctaLabel)
        );
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private function renderQuote(array $block): string
    {
        $text = trim((string) ($block['text'] ?? ''));
        $source = trim((string) ($block['source'] ?? 'Source Attribution'));
        $citeUrl = trim((string) ($block['cite_url'] ?? ''));

        if ($text === '') {
            return '';
        }

        $citeAttr = $this->isSafeUrl($citeUrl) ? ' cite="'.e($citeUrl).'"' : '';

        return sprintf(
            '<figure class="article-quote"><blockquote%s><p>%s</p></blockquote><figcaption><cite>%s</cite></figcaption></figure>',
            $citeAttr,
            $this->formatInline($text),
            e($source)
        );
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private function renderCode(array $block, int $index): string
    {
        $caption = trim((string) ($block['caption'] ?? 'Code & Telemetry Reference'));
        $language = preg_replace('/[^a-z0-9_-]/i', '', (string) ($block['language'] ?? 'json')) ?: 'plaintext';
        $code = (string) ($block['code'] ?? '');
        $captionId = 'code-caption-'.$index.'-'.Str::slug($caption);

        if (trim($code) === '') {
            return '';
        }

        return sprintf(
            '<figure class="article-code-block"><figcaption id="%s">%s</figcaption><pre role="region" aria-labelledby="%s" tabindex="0"><code class="language-%s">%s</code></pre></figure>',
            e($captionId),
            e($caption),
            e($captionId),
            e($language),
            e($code)
        );
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private function renderStatGrid(array $block): string
    {
        $title = trim((string) ($block['title'] ?? 'Telemetry Summary'));
        $items = is_array($block['items'] ?? null) ? $block['items'] : [];
        if ($items === []) {
            return '';
        }

        $cards = '';
        foreach ($items as $item) {
            $label = trim((string) ($item['label'] ?? ''));
            $value = trim((string) ($item['value'] ?? ''));
            $note = trim((string) ($item['note'] ?? ''));
            if ($label === '' || $value === '') {
                continue;
            }
            $cards .= sprintf(
                '<div class="article-stat-card"><dt>%s</dt><dd class="stat-val">%s</dd><dd class="stat-note">%s</dd></div>',
                e($label),
                e($value),
                e($note)
            );
        }

        if ($cards === '') {
            return '';
        }

        return sprintf(
            '<section class="article-stat-grid-wrap" aria-label="%s"><p class="stat-grid-heading">%s</p><dl class="article-stat-grid">%s</dl></section>',
            e($title),
            e($title),
            $cards
        );
    }

    /**
     * Escape raw input first, then transform safe markdown-lite spans (links, bold, italic, inline code).
     */
    private function formatInline(string $text): string
    {
        $escaped = e($text);

        // Inline code: `snippet`
        $escaped = preg_replace('/`([^`]+)`/', '<code>$1</code>', $escaped) ?? $escaped;

        // Bold: **text**
        $escaped = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $escaped) ?? $escaped;

        // Italic: *text*
        $escaped = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/', '<em>$1</em>', $escaped) ?? $escaped;

        // Safe Links: [label](url) allowing relative `/` or `https://` / `http://`
        $escaped = preg_replace_callback(
            '/\[([^\]]+)\]\(([^)]+)\)/',
            function (array $matches): string {
                $label = $matches[1];
                $rawUrl = html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5);
                if (! $this->isSafeUrl($rawUrl)) {
                    return $label;
                }

                $isExternal = str_starts_with($rawUrl, 'http');
                $rel = $isExternal ? ' rel="noopener noreferrer" target="_blank"' : '';

                return '<a href="'.e($rawUrl).'" class="article-link"'.$rel.'>'.$label.'</a>';
            },
            $escaped
        ) ?? $escaped;

        return $escaped;
    }

    private function isSafeUrl(string $url): bool
    {
        $trimmed = trim($url);
        if ($trimmed === '') {
            return false;
        }

        if (str_starts_with($trimmed, '/')) {
            return ! str_starts_with($trimmed, '//');
        }

        $scheme = parse_url($trimmed, PHP_URL_SCHEME);

        return in_array($scheme, ['http', 'https'], true);
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private function flattenTableText(array $block): string
    {
        $parts = [(string) ($block['caption'] ?? '')];
        foreach (($block['headers'] ?? []) as $header) {
            $parts[] = (string) $header;
        }
        foreach (($block['rows'] ?? []) as $row) {
            if (is_array($row)) {
                foreach ($row as $cell) {
                    $parts[] = (string) $cell;
                }
            }
        }

        return implode(' ', $parts);
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private function flattenFaqText(array $block): string
    {
        $parts = [(string) ($block['heading'] ?? '')];
        foreach (($block['items'] ?? []) as $item) {
            $parts[] = (string) ($item['question'] ?? '');
            $parts[] = (string) ($item['answer'] ?? '');
        }

        return implode(' ', $parts);
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private function flattenStatGridText(array $block): string
    {
        $parts = [(string) ($block['title'] ?? '')];
        foreach (($block['items'] ?? []) as $item) {
            $parts[] = (string) ($item['label'] ?? '');
            $parts[] = (string) ($item['value'] ?? '');
            $parts[] = (string) ($item['note'] ?? '');
        }

        return implode(' ', $parts);
    }
}
