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

final readonly class PostsController
{
    public function index(MicrositeDataService $data, SeoService $seo): View
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');

        return view('blog.index', [
            'categories' => $data->categories(),
            'tags' => $data->tags(),
            'seoMeta' => $seo->buildPageMeta(
                title: 'Vice Telemetry Blog — Deep Technical GTA VI Dispatches | GTAVI.PRO',
                description: 'Long-form independent articles covering GTA VI download sizes, pricing economics, RAGE 9 rendering, Take-Two filings, and Leonida cartography.',
                canonicalUrl: $siteUrl.'/gta',
                breadcrumbs: [
                    ['name' => 'Home', 'url' => $siteUrl.'/'],
                    ['name' => 'News & Features', 'url' => $siteUrl.'/gta'],
                ],
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
            ),
        ]);
    }

    public function category(
        string $slug,
        PostService $posts,
        MicrositeDataService $data,
        SeoService $seo,
    ): View {
        $category = Category::query()->where('slug', $slug)->firstOrFail();
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');

        return view('blog.archive', [
            'archiveType' => 'Category',
            'archiveTitle' => $category->name,
            'archiveDescription' => $category->description ?? 'Independent GTAVI.PRO analysis archive.',
            'accentColor' => $category->accent_color,
            'posts' => $posts->listPublished(['category' => $category->slug], 12),
            'categories' => $data->categories(),
            'seoMeta' => $seo->buildPageMeta(
                title: $category->meta_title ?: $category->name.' | GTAVI.PRO Blog',
                description: $category->meta_description ?: ($category->description ?? ''),
                canonicalUrl: $siteUrl.'/gta/category/'.$category->slug,
                breadcrumbs: [
                    ['name' => 'Home', 'url' => $siteUrl.'/'],
                    ['name' => 'News & Features', 'url' => $siteUrl.'/gta'],
                    ['name' => $category->name, 'url' => $siteUrl.'/gta/category/'.$category->slug],
                ],
            ),
        ]);
    }

    public function tag(
        string $slug,
        PostService $posts,
        MicrositeDataService $data,
        SeoService $seo,
    ): View {
        $tag = Tag::query()->where('slug', $slug)->firstOrFail();
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');

        return view('blog.archive', [
            'archiveType' => 'Topic Tag',
            'archiveTitle' => '#'.$tag->name,
            'archiveDescription' => "All published GTAVI.PRO technical reports and guides tagged {$tag->name}.",
            'accentColor' => '#ff1e7a',
            'posts' => $posts->listPublished(['tag' => $tag->slug], 12),
            'categories' => $data->categories(),
            'seoMeta' => $seo->buildPageMeta(
                title: $tag->name.' Articles & Telemetry | GTAVI.PRO',
                description: "Explore GTAVI.PRO deep dives and calculators tagged {$tag->name}. Unofficial fan and hardware publication.",
                canonicalUrl: $siteUrl.'/gta/tag/'.$tag->slug,
                breadcrumbs: [
                    ['name' => 'Home', 'url' => $siteUrl.'/'],
                    ['name' => 'News & Features', 'url' => $siteUrl.'/gta'],
                    ['name' => $tag->name, 'url' => $siteUrl.'/gta/tag/'.$tag->slug],
                ],
            ),
        ]);
    }
}
