<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tag;
use App\Services\ContentBlockService;
use App\Services\MicrositeDataService;
use App\Services\PostService;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final readonly class PostsController
{
    public function index(Request $request, MicrositeDataService $data, SeoService $seo): View
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');
        $hasActiveFilters = collect(['q', 'category', 'tag', 'badge', 'sort'])
            ->contains(fn (string $key): bool => trim((string) $request->query($key, '')) !== '');
        $page = max(1, (int) $request->query('page', 1));

        $title = 'Vice Telemetry Blog — Deep Technical GTA VI Dispatches | GTAVI.PRO';
        $canonicalUrl = $siteUrl.'/gta';
        $robots = 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

        if ($hasActiveFilters) {
            $robots = 'noindex, follow';
        } elseif ($page > 1) {
            $title = "Vice Telemetry Blog — Page {$page} | GTAVI.PRO";
            $canonicalUrl = "{$siteUrl}/gta?page={$page}";
        }

        return view('blog.index', [
            'categories' => $data->categories(),
            'tags' => $data->tags(),
            'seoMeta' => $seo->buildPageMeta(
                title: $title,
                description: 'Long-form independent articles covering GTA VI download sizes, pricing economics, RAGE 9 rendering, Take-Two filings, and Leonida cartography.',
                canonicalUrl: $canonicalUrl,
                breadcrumbs: [
                    ['name' => 'Home', 'url' => $siteUrl.'/'],
                    ['name' => 'News & Features', 'url' => $siteUrl.'/gta'],
                ],
                robots: $robots,
                ogImage: $siteUrl.'/og/blog.svg',
            ),
        ]);
    }

    public function show(
        string $slug,
        PostService $posts,
        ContentBlockService $blocks,
        SeoService $seo,
    ): View {
        $post = $posts->findPublishedBySlug($slug);
        abort_if($post === null, 404);

        $post->increment('view_count');

        $contentBlocks = $post->content_blocks ?? [];
        $renderedHtml = $post->rendered_html ?: $blocks->renderBlocks($contentBlocks);
        $toc = $blocks->extractTableOfContents($contentBlocks);
        $faqItems = $post->faq_items ?? $blocks->extractFaqItems($contentBlocks);
        $related = $posts->relatedPosts($post, 3);
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');
        $canonical = $post->canonical_url ?: $siteUrl.'/gta/'.$post->slug;

        return view('blog.show', [
            'post' => $post,
            'renderedHtml' => $renderedHtml,
            'toc' => $toc,
            'relatedPosts' => $related,
            'seoMeta' => $seo->buildPageMeta(
                title: $post->meta_title ?: $post->title.' | GTAVI.PRO',
                description: $post->meta_description ?: $post->excerpt,
                canonicalUrl: $canonical,
                ogType: 'article',
                breadcrumbs: [
                    ['name' => 'Home', 'url' => $siteUrl.'/'],
                    ['name' => 'News & Features', 'url' => $siteUrl.'/gta'],
                    ['name' => $post->category->name, 'url' => $siteUrl.'/gta/category/'.$post->category->slug],
                    ['name' => $post->title, 'url' => $canonical],
                ],
                faqItems: $faqItems,
                post: $post,
                ogImage: $siteUrl.'/og/'.$post->slug.'.svg',
                markdownUrl: $siteUrl.'/gta/'.$post->slug.'.md',
            ),
        ]);
    }

    public function showMarkdown(
        string $slug,
        PostService $posts,
        ContentBlockService $blocks,
    ): Response {
        $post = $posts->findPublishedBySlug($slug);
        abort_if($post === null, 404);

        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');
        $canonical = $post->canonical_url ?: $siteUrl.'/gta/'.$post->slug;
        $publishedAt = ($post->published_at ?? $post->created_at)?->toIso8601String() ?? '';
        $updatedAt = ($post->updated_at ?? $post->published_at ?? $post->created_at)?->toIso8601String() ?? '';
        $bodyMarkdown = $blocks->renderMarkdown($post->content_blocks ?? []);

        $escapeYaml = static fn (string $val): string => '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $val).'"';

        $lines = [
            '---',
            'title: '.$escapeYaml($post->title),
            'slug: '.$escapeYaml($post->slug),
            'canonical_url: '.$escapeYaml($canonical),
            'category: '.$escapeYaml($post->category->name),
            'published_at: '.$escapeYaml($publishedAt),
            'updated_at: '.$escapeYaml($updatedAt),
            'author: '.$escapeYaml("{$post->author_name} ({$post->author_role})"),
            'verification_status: '.$escapeYaml($post->verificationLabel()),
            'word_count: '.$post->word_count,
            'disclaimer: "Independent fan & technical telemetry publication. Not affiliated with Rockstar Games or Take-Two Interactive."',
            '---',
            '',
            "# {$post->title}",
        ];

        if ($post->subtitle) {
            $lines[] = '';
            $lines[] = "> {$post->subtitle}";
        }

        $lines[] = '';
        $lines[] = $bodyMarkdown;

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
            'X-Robots-Tag' => 'noindex, follow',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }

    public function category(
        Request $request,
        string $slug,
        PostService $posts,
        MicrositeDataService $data,
        SeoService $seo,
    ): View {
        $category = Category::query()->where('slug', $slug)->firstOrFail();
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');
        $page = max(1, (int) $request->query('page', 1));
        $baseTitle = $category->meta_title ?: $category->name.' | GTAVI.PRO Blog';
        $title = $page > 1 ? "{$baseTitle} — Page {$page}" : $baseTitle;
        $canonicalUrl = $siteUrl.'/gta/category/'.$category->slug.($page > 1 ? "?page={$page}" : '');

        return view('blog.archive', [
            'archiveType' => 'Category',
            'archiveTitle' => $category->name,
            'archiveDescription' => $category->description ?? 'Independent GTAVI.PRO analysis archive.',
            'accentColor' => $category->accent_color,
            'posts' => $posts->listPublished(['category' => $category->slug], 12),
            'categories' => $data->categories(),
            'seoMeta' => $seo->buildPageMeta(
                title: $title,
                description: $category->meta_description ?: ($category->description ?? ''),
                canonicalUrl: $canonicalUrl,
                breadcrumbs: [
                    ['name' => 'Home', 'url' => $siteUrl.'/'],
                    ['name' => 'News & Features', 'url' => $siteUrl.'/gta'],
                    ['name' => $category->name, 'url' => $siteUrl.'/gta/category/'.$category->slug],
                ],
                ogImage: $siteUrl.'/og/blog.svg',
            ),
        ]);
    }

    public function tag(
        Request $request,
        string $slug,
        PostService $posts,
        MicrositeDataService $data,
        SeoService $seo,
    ): View {
        $tag = Tag::query()->where('slug', $slug)->firstOrFail();
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');
        $page = max(1, (int) $request->query('page', 1));
        $baseTitle = $tag->name.' Articles & Telemetry | GTAVI.PRO';
        $title = $page > 1 ? "{$baseTitle} — Page {$page}" : $baseTitle;
        $canonicalUrl = $siteUrl.'/gta/tag/'.$tag->slug.($page > 1 ? "?page={$page}" : '');

        return view('blog.archive', [
            'archiveType' => 'Topic Tag',
            'archiveTitle' => '#'.$tag->name,
            'archiveDescription' => "All published GTAVI.PRO technical reports and guides tagged {$tag->name}.",
            'accentColor' => '#ff1e7a',
            'posts' => $posts->listPublished(['tag' => $tag->slug], 12),
            'categories' => $data->categories(),
            'seoMeta' => $seo->buildPageMeta(
                title: $title,
                description: "Explore GTAVI.PRO deep dives and calculators tagged {$tag->name}. Unofficial fan and hardware publication.",
                canonicalUrl: $canonicalUrl,
                breadcrumbs: [
                    ['name' => 'Home', 'url' => $siteUrl.'/'],
                    ['name' => 'News & Features', 'url' => $siteUrl.'/gta'],
                    ['name' => $tag->name, 'url' => $siteUrl.'/gta/tag/'.$tag->slug],
                ],
                ogImage: $siteUrl.'/og/blog.svg',
            ),
        ]);
    }
}
