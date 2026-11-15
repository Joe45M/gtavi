<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Post;
use App\Models\PostRevision;
use App\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class PostService
{
    public function __construct(
        private ContentBlockService $contentBlockService,
        private SeoService $seoService,
    ) {}

    /**
     * Paginate published posts with optional search, category, tag, and verification filters.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Post>
     */
    public function listPublished(array $filters = [], int $perPage = 9): LengthAwarePaginator
    {
        $query = Post::query()
            ->published()
            ->with(['category', 'tags']);

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('focus_keyword', 'like', "%{$search}%");
            });
        }

        $categorySlug = trim((string) ($filters['category'] ?? ''));
        if ($categorySlug !== '') {
            $query->whereHas('category', fn (Builder $q): Builder => $q->where('slug', $categorySlug));
        }

        $tagSlug = trim((string) ($filters['tag'] ?? ''));
        if ($tagSlug !== '') {
            $query->whereHas('tags', fn (Builder $q): Builder => $q->where('slug', $tagSlug));
        }

        $badge = trim((string) ($filters['verification_badge'] ?? ''));
        if ($badge !== '') {
            $query->where('verification_badge', $badge);
        }

        $sort = (string) ($filters['sort'] ?? 'latest');
        match ($sort) {
            'seo' => $query->orderByDesc('seo_score')->orderByDesc('published_at'),
            'popular' => $query->orderByDesc('view_count')->orderByDesc('published_at'),
            default => $query->orderByDesc('is_featured')->orderByDesc('published_at'),
        };

        return $query->paginate($perPage);
    }

    public function findPublishedBySlug(string $slug): ?Post
    {
        return Post::query()
            ->published()
            ->with(['category', 'tags'])
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @return Collection<int, Post>
     */
    public function relatedPosts(Post $post, int $limit = 3): Collection
    {
        return Post::query()
            ->published()
            ->with(['category', 'tags'])
            ->whereKeyNot($post->getKey())
            ->orderByRaw('CASE WHEN category_id = ? THEN 0 ELSE 1 END', [$post->category_id])
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Persist a post from the WordPress-style Studio block editor and create a revision snapshot.
     *
     * @param  array<string, mixed>  $payload
     */
    public function saveFromStudio(array $payload, ?Post $post = null, ?string $editorNote = null): Post
    {
        return DB::transaction(function () use ($payload, $post, $editorNote): Post {
            $blocks = is_array($payload['content_blocks'] ?? null) ? array_values($payload['content_blocks']) : [];
            $renderedHtml = $this->contentBlockService->renderBlocks($blocks);
            $faqItems = $this->contentBlockService->extractFaqItems($blocks);
            $slug = Str::slug((string) ($payload['slug'] ?? $payload['title'] ?? 'untitled-post'));

            $analysis = $this->seoService->analyzeContent([
                'title' => $payload['title'] ?? '',
                'slug' => $slug,
                'excerpt' => $payload['excerpt'] ?? '',
                'meta_title' => $payload['meta_title'] ?? '',
                'meta_description' => $payload['meta_description'] ?? '',
                'focus_keyword' => $payload['focus_keyword'] ?? '',
                'content_blocks' => $blocks,
            ]);

            $status = (string) ($payload['status'] ?? 'draft');
            $publishedAt = $payload['published_at'] ?? null;
            if ($status === 'published' && $publishedAt === null) {
                $publishedAt = $post?->published_at ?? now();
            }

            $attributes = [
                'category_id' => (int) $payload['category_id'],
                'title' => (string) $payload['title'],
                'slug' => $this->ensureUniqueSlug($slug, $post?->id),
                'subtitle' => $payload['subtitle'] ?? null,
                'excerpt' => (string) $payload['excerpt'],
                'content_blocks' => $blocks,
                'rendered_html' => $renderedHtml,
                'featured_image_label' => $payload['featured_image_label'] ?? null,
                'featured_image_gradient' => (string) ($payload['featured_image_gradient'] ?? 'from-pink-600 via-purple-700 to-indigo-950'),
                'author_name' => (string) ($payload['author_name'] ?? 'joe'),
                'author_role' => (string) ($payload['author_role'] ?? 'Lead Technical Analyst'),
                'status' => $status,
                'verification_badge' => (string) ($payload['verification_badge'] ?? 'technical_projection'),
                'is_featured' => (bool) ($payload['is_featured'] ?? false),
                'reading_time_minutes' => $analysis['reading_time_minutes'],
                'word_count' => $analysis['word_count'],
                'focus_keyword' => $payload['focus_keyword'] ?? null,
                'meta_title' => $payload['meta_title'] ?? null,
                'meta_description' => $payload['meta_description'] ?? null,
                'canonical_url' => $payload['canonical_url'] ?? null,
                'og_title' => $payload['og_title'] ?? null,
                'og_description' => $payload['og_description'] ?? null,
                'schema_type' => (string) ($payload['schema_type'] ?? 'BlogPosting'),
                'faq_items' => $faqItems,
                'seo_score' => $analysis['seo_score'],
                'readability_score' => $analysis['readability_score'],
                'ai_model' => $payload['ai_model'] ?? $post?->ai_model,
                'ai_prompt_tokens' => array_key_exists('ai_prompt_tokens', $payload) ? ($payload['ai_prompt_tokens'] !== null ? (int) $payload['ai_prompt_tokens'] : null) : $post?->ai_prompt_tokens,
                'ai_completion_tokens' => array_key_exists('ai_completion_tokens', $payload) ? ($payload['ai_completion_tokens'] !== null ? (int) $payload['ai_completion_tokens'] : null) : $post?->ai_completion_tokens,
                'ai_thinking_tokens' => array_key_exists('ai_thinking_tokens', $payload) ? ($payload['ai_thinking_tokens'] !== null ? (int) $payload['ai_thinking_tokens'] : null) : $post?->ai_thinking_tokens,
                'ai_search_queries' => array_key_exists('ai_search_queries', $payload) ? ($payload['ai_search_queries'] !== null ? (int) $payload['ai_search_queries'] : null) : $post?->ai_search_queries,
                'ai_estimated_cost_usd' => array_key_exists('ai_estimated_cost_usd', $payload) ? ($payload['ai_estimated_cost_usd'] !== null ? (float) $payload['ai_estimated_cost_usd'] : null) : $post?->ai_estimated_cost_usd,
                'published_at' => $publishedAt,
            ];

            if ($post !== null) {
                $oldSlug = $post->slug;
                $post->update($attributes);
                Cache::forget("posts.published.{$oldSlug}");
            } else {
                $post = Post::query()->create($attributes);
            }

            $tagNames = $payload['tags'] ?? [];
            if (is_array($tagNames)) {
                $tagIds = collect($tagNames)
                    ->map(fn (mixed $name): string => trim((string) $name))
                    ->filter(fn (string $name): bool => $name !== '')
                    ->map(function (string $name): int {
                        $tag = Tag::query()->firstOrCreate(
                            ['slug' => Str::slug($name)],
                            ['name' => $name]
                        );

                        return $tag->id;
                    })
                    ->all();

                $post->tags()->sync($tagIds);
            }

            PostRevision::query()->create([
                'post_id' => $post->id,
                'title' => $post->title,
                'content_blocks' => $blocks,
                'seo_score' => $analysis['seo_score'],
                'editor_note' => $editorNote ?: 'Saved via GTAVI.PRO Studio',
                'created_at' => now(),
            ]);

            Cache::forget("posts.published.{$post->slug}");
            Cache::forget('microsite.home_summary');

            return $post->load(['category', 'tags', 'revisions']);
        });
    }

    public function restoreRevision(Post $post, PostRevision $revision): Post
    {
        return $this->saveFromStudio([
            'category_id' => $post->category_id,
            'title' => $revision->title,
            'slug' => $post->slug,
            'subtitle' => $post->subtitle,
            'excerpt' => $post->excerpt,
            'content_blocks' => $revision->content_blocks,
            'featured_image_label' => $post->featured_image_label,
            'featured_image_gradient' => $post->featured_image_gradient,
            'author_name' => $post->author_name,
            'author_role' => $post->author_role,
            'status' => $post->status,
            'verification_badge' => $post->verification_badge,
            'is_featured' => $post->is_featured,
            'focus_keyword' => $post->focus_keyword,
            'meta_title' => $post->meta_title,
            'meta_description' => $post->meta_description,
            'canonical_url' => $post->canonical_url,
            'og_title' => $post->og_title,
            'og_description' => $post->og_description,
            'schema_type' => $post->schema_type,
            'published_at' => $post->published_at,
            'tags' => $post->tags->pluck('name')->all(),
        ], $post, "Restored revision #{$revision->id}");
    }

    private function ensureUniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'gtavi-dispatch';
        $candidate = $slug;
        $counter = 2;

        while (
            Post::query()
                ->where('slug', $candidate)
                ->when($ignoreId !== null, fn (Builder $q): Builder => $q->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $candidate = $slug.'-'.$counter;
            $counter++;
        }

        return $candidate;
    }
}
