<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostRevision;
use App\Services\ContentBlockService;
use App\Services\PostService;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class StudioPostEditor extends Component
{
    public ?int $postId = null;

    public int $categoryId = 0;

    public string $title = '';

    public string $slug = '';

    public string $subtitle = '';

    public string $excerpt = '';

    public string $featuredImageLabel = '';

    public string $featuredImageGradient = 'from-pink-600 via-purple-800 to-indigo-950';

    public string $authorName = 'joe';

    public string $authorRole = 'Lead Technical Analyst';

    public string $status = 'draft';

    public string $verificationBadge = 'technical_projection';

    public bool $isFeatured = false;

    public string $focusKeyword = '';

    public string $metaTitle = '';

    public string $metaDescription = '';

    public string $canonicalUrl = '';

    public string $ogTitle = '';

    public string $ogDescription = '';

    public string $schemaType = 'BlogPosting';

    public string $tagsInput = '';

    public string $editorNote = '';

    public string $serpDevice = 'desktop';

    public string $sidebarTab = 'seo';

    public ?string $saveBanner = null;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $contentBlocks = [];

    public function mount(?Post $post = null): void
    {
        abort_unless(auth()->user()?->can('access studio') ?? false, 403);

        if ($post !== null && $post->exists) {
            abort_unless(auth()->user()?->can('edit posts') ?? false, 403);
        } else {
            abort_unless(auth()->user()?->can('create posts') ?? false, 403);
        }

        $categories = Category::query()->ordered()->get();
        $defaultCategoryId = (int) ($categories->first()?->id ?? 1);

        if ($post !== null && $post->exists) {
            $post->loadMissing(['tags']);
            $this->postId = $post->id;
            $this->categoryId = $post->category_id;
            $this->title = $post->title;
            $this->slug = $post->slug;
            $this->subtitle = (string) ($post->subtitle ?? '');
            $this->excerpt = $post->excerpt;
            $this->featuredImageLabel = (string) ($post->featured_image_label ?? '');
            $this->featuredImageGradient = $post->featured_image_gradient;
            $this->authorName = $post->author_name;
            $this->authorRole = $post->author_role;
            $this->status = $post->status;
            $this->verificationBadge = $post->verification_badge;
            $this->isFeatured = $post->is_featured;
            $this->focusKeyword = (string) ($post->focus_keyword ?? '');
            $this->metaTitle = (string) ($post->meta_title ?? '');
            $this->metaDescription = (string) ($post->meta_description ?? '');
            $this->canonicalUrl = (string) ($post->canonical_url ?? '');
            $this->ogTitle = (string) ($post->og_title ?? '');
            $this->ogDescription = (string) ($post->og_description ?? '');
            $this->schemaType = $post->schema_type;
            $this->tagsInput = $post->tags->pluck('name')->implode(', ');
            $this->contentBlocks = $this->normalizeBlocks($post->content_blocks ?? []);

            return;
        }

        $this->categoryId = $defaultCategoryId;
        $this->title = 'New GTA VI Technical Telemetry Dispatch';
        $this->slug = 'new-gta-vi-technical-telemetry-dispatch';
        $this->subtitle = 'Independent hardware, pricing, or Leonida analysis on GTAVI.PRO.';
        $this->excerpt = 'Write a crisp, high-CTR summary explaining what readers will calculate or verify in this independent GTAVI.PRO dispatch.';
        $this->focusKeyword = 'GTA VI telemetry';
        $this->featuredImageLabel = 'TELEMETRY // VICE EDITION';
        $this->tagsInput = 'RAGE 9 Engine, Vice City';

        $blockService = app(ContentBlockService::class);
        $this->contentBlocks = [
            $blockService->defaultBlockTemplate('callout'),
            $blockService->defaultBlockTemplate('paragraph'),
            $blockService->defaultBlockTemplate('heading'),
            $blockService->defaultBlockTemplate('spec_table'),
        ];
    }

    public function generateSlug(): void
    {
        $this->slug = Str::slug($this->title);
    }

    public function autoFillSeo(): void
    {
        if (trim($this->slug) === '') {
            $this->slug = Str::slug($this->title);
        }

        $cleanTitle = Str::limit(trim($this->title), 54, '');
        $this->metaTitle = $cleanTitle.' | GTAVI.PRO';
        $this->metaDescription = Str::limit(trim($this->excerpt), 156, '...');
        $this->ogTitle = $cleanTitle;
        $this->ogDescription = $this->metaDescription;
        $this->saveBanner = 'Auto-populated Meta Title, Description & OpenGraph tags from article copy.';
    }

    public function addBlock(string $type, ?int $afterIndex = null): void
    {
        $template = app(ContentBlockService::class)->defaultBlockTemplate($type);

        if ($afterIndex !== null && isset($this->contentBlocks[$afterIndex])) {
            array_splice($this->contentBlocks, $afterIndex + 1, 0, [$template]);
        } else {
            $this->contentBlocks[] = $template;
        }

        $this->contentBlocks = array_values($this->contentBlocks);
    }

    public function moveBlockUp(int $index): void
    {
        if ($index <= 0 || ! isset($this->contentBlocks[$index])) {
            return;
        }

        $temp = $this->contentBlocks[$index - 1];
        $this->contentBlocks[$index - 1] = $this->contentBlocks[$index];
        $this->contentBlocks[$index] = $temp;
        $this->contentBlocks = array_values($this->contentBlocks);
    }

    public function moveBlockDown(int $index): void
    {
        if (! isset($this->contentBlocks[$index], $this->contentBlocks[$index + 1])) {
            return;
        }

        $temp = $this->contentBlocks[$index + 1];
        $this->contentBlocks[$index + 1] = $this->contentBlocks[$index];
        $this->contentBlocks[$index] = $temp;
        $this->contentBlocks = array_values($this->contentBlocks);
    }

    public function duplicateBlock(int $index): void
    {
        if (! isset($this->contentBlocks[$index])) {
            return;
        }

        $copy = $this->contentBlocks[$index];
        $copy['id'] = (string) Str::uuid();
        array_splice($this->contentBlocks, $index + 1, 0, [$copy]);
        $this->contentBlocks = array_values($this->contentBlocks);
    }

    public function removeBlock(int $index): void
    {
        if (count($this->contentBlocks) <= 1 || ! isset($this->contentBlocks[$index])) {
            return;
        }

        unset($this->contentBlocks[$index]);
        $this->contentBlocks = array_values($this->contentBlocks);
    }

    public function addTableRow(int $blockIndex): void
    {
        if (($this->contentBlocks[$blockIndex]['type'] ?? '') !== 'spec_table') {
            return;
        }

        $colCount = max(1, count($this->contentBlocks[$blockIndex]['headers'] ?? []));
        $this->contentBlocks[$blockIndex]['rows'][] = array_fill(0, $colCount, 'New Data');
    }

    public function removeTableRow(int $blockIndex, int $rowIndex): void
    {
        $rows = $this->contentBlocks[$blockIndex]['rows'] ?? [];
        if (count($rows) <= 1 || ! isset($rows[$rowIndex])) {
            return;
        }

        unset($this->contentBlocks[$blockIndex]['rows'][$rowIndex]);
        $this->contentBlocks[$blockIndex]['rows'] = array_values($this->contentBlocks[$blockIndex]['rows']);
    }

    public function addTableColumn(int $blockIndex): void
    {
        if (($this->contentBlocks[$blockIndex]['type'] ?? '') !== 'spec_table') {
            return;
        }

        $this->contentBlocks[$blockIndex]['headers'][] = 'Metric';
        foreach ($this->contentBlocks[$blockIndex]['rows'] as $rIdx => $row) {
            $this->contentBlocks[$blockIndex]['rows'][$rIdx][] = 'TBD';
        }
    }

    public function addFaqItem(int $blockIndex): void
    {
        if (($this->contentBlocks[$blockIndex]['type'] ?? '') !== 'faq') {
            return;
        }

        $this->contentBlocks[$blockIndex]['items'][] = [
            'question' => 'New Reader Question?',
            'answer' => 'Provide a direct, concise answer optimized for search featured snippets.',
        ];
    }

    public function removeFaqItem(int $blockIndex, int $itemIndex): void
    {
        $items = $this->contentBlocks[$blockIndex]['items'] ?? [];
        if (count($items) <= 1 || ! isset($items[$itemIndex])) {
            return;
        }

        unset($this->contentBlocks[$blockIndex]['items'][$itemIndex]);
        $this->contentBlocks[$blockIndex]['items'] = array_values($this->contentBlocks[$blockIndex]['items']);
    }

    public function addStatItem(int $blockIndex): void
    {
        if (($this->contentBlocks[$blockIndex]['type'] ?? '') !== 'stat_grid') {
            return;
        }

        $this->contentBlocks[$blockIndex]['items'][] = [
            'label' => 'New KPI Metric',
            'value' => '100%',
            'note' => 'Independent projection',
        ];
    }

    public function removeStatItem(int $blockIndex, int $itemIndex): void
    {
        $items = $this->contentBlocks[$blockIndex]['items'] ?? [];
        if (count($items) <= 1 || ! isset($items[$itemIndex])) {
            return;
        }

        unset($this->contentBlocks[$blockIndex]['items'][$itemIndex]);
        $this->contentBlocks[$blockIndex]['items'] = array_values($this->contentBlocks[$blockIndex]['items']);
    }

    public function insertMarkdownToken(int $blockIndex, string $tokenType): void
    {
        if (! isset($this->contentBlocks[$blockIndex]['text'])) {
            return;
        }

        $snippet = match ($tokenType) {
            'bold' => ' **highlighted metric**',
            'italic' => ' *verified note*',
            'code' => ' `RAGE-9`',
            'link_calc' => ' [GTA VI Download Calculator](/calculators?tab=download)',
            'link_prices' => ' [Editions & Pricing Matrix](/prices-editions)',
            'link_disclaimer' => ' [Non-Affiliation Policy](/disclaimer)',
            default => '',
        };

        $this->contentBlocks[$blockIndex]['text'] = trim((string) $this->contentBlocks[$blockIndex]['text']).$snippet;
    }

    public function savePost(string $targetStatus = 'keep'): void
    {
        abort_unless(auth()->user()?->can($this->postId !== null ? 'edit posts' : 'create posts') ?? false, 403);

        if (in_array($targetStatus, ['draft', 'published', 'scheduled'], true)) {
            $this->status = $targetStatus;
        }

        if (trim($this->slug) === '') {
            $this->slug = Str::slug($this->title);
        }

        $validated = $this->validate([
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'min:5', 'max:180'],
            'slug' => [
                'required',
                'string',
                'max:200',
                Rule::unique('posts', 'slug')->ignore($this->postId),
            ],
            'excerpt' => ['required', 'string', 'min:20', 'max:500'],
            'status' => ['required', Rule::in(['draft', 'published', 'scheduled'])],
            'verificationBadge' => ['required', Rule::in([
                'confirmed',
                'official_filing',
                'technical_projection',
                'community_speculation',
            ])],
            'schemaType' => ['required', Rule::in(['BlogPosting', 'NewsArticle', 'TechArticle', 'FAQPage'])],
            'contentBlocks' => ['required', 'array', 'min:1'],
        ]);

        $post = $this->postId !== null ? Post::query()->find($this->postId) : null;
        $tags = array_values(array_filter(array_map('trim', explode(',', $this->tagsInput))));

        $saved = app(PostService::class)->saveFromStudio([
            'category_id' => $validated['categoryId'],
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'subtitle' => $this->subtitle ?: null,
            'excerpt' => $validated['excerpt'],
            'content_blocks' => $this->contentBlocks,
            'featured_image_label' => $this->featuredImageLabel ?: null,
            'featured_image_gradient' => $this->featuredImageGradient,
            'author_name' => $this->authorName ?: 'joe',
            'author_role' => $this->authorRole ?: 'Lead Technical Analyst',
            'status' => $validated['status'],
            'verification_badge' => $validated['verificationBadge'],
            'is_featured' => $this->isFeatured,
            'focus_keyword' => $this->focusKeyword ?: null,
            'meta_title' => $this->metaTitle ?: null,
            'meta_description' => $this->metaDescription ?: null,
            'canonical_url' => $this->canonicalUrl ?: null,
            'og_title' => $this->ogTitle ?: null,
            'og_description' => $this->ogDescription ?: null,
            'schema_type' => $validated['schemaType'],
            'tags' => $tags,
        ], $post, $this->editorNote ?: null);

        $this->postId = $saved->id;
        $this->slug = $saved->slug;
        $this->editorNote = '';
        $this->saveBanner = sprintf(
            'Saved "%s" (%s) — SEO Score: %d/100 • Revision logged.',
            $saved->title,
            strtoupper($saved->status),
            $saved->seo_score
        );
    }

    public function restoreRevision(int $revisionId): void
    {
        abort_unless(auth()->user()?->can('edit posts') ?? false, 403);

        if ($this->postId === null) {
            return;
        }

        $post = Post::query()->findOrFail($this->postId);
        $revision = PostRevision::query()
            ->where('post_id', $post->id)
            ->findOrFail($revisionId);

        $restored = app(PostService::class)->restoreRevision($post, $revision);
        $this->title = $restored->title;
        $this->contentBlocks = $this->normalizeBlocks($restored->content_blocks ?? []);
        $this->saveBanner = "Restored revision #{$revision->id} and logged a fresh snapshot.";
    }

    /**
     * @return array<string, mixed>
     */
    #[Computed]
    public function seoAnalysis(): array
    {
        return app(SeoService::class)->analyzeContent([
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'meta_title' => $this->metaTitle,
            'meta_description' => $this->metaDescription,
            'focus_keyword' => $this->focusKeyword,
            'content_blocks' => $this->contentBlocks,
        ]);
    }

    #[Computed]
    public function previewHtml(): string
    {
        return app(ContentBlockService::class)->renderBlocks($this->contentBlocks);
    }

    /**
     * @return Collection<int, PostRevision>
     */
    #[Computed]
    public function revisions(): Collection
    {
        if ($this->postId === null) {
            return new Collection;
        }

        return PostRevision::query()
            ->where('post_id', $this->postId)
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();
    }

    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::query()->ordered()->get();
    }

    public function render(): View
    {
        return view('livewire.studio-post-editor');
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    private function normalizeBlocks(array $blocks): array
    {
        return array_values(array_map(static function (array $block): array {
            if (! isset($block['id'])) {
                $block['id'] = (string) Str::uuid();
            }

            return $block;
        }, $blocks));
    }
}
