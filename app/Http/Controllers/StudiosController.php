<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\GenerateAiPostRequest;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Services\GeminiArticleService;
use App\Services\PostService;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class StudiosController
{
    public function index(SeoService $seo): View
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');
        $posts = Post::query()
            ->with(['category', 'tags'])
            ->withCount('revisions')
            ->orderByDesc('updated_at')
            ->paginate(15);

        return view('studio.index', [
            'posts' => $posts,
            'seoMeta' => $seo->buildPageMeta(
                title: 'Vice Editorial Studio — WordPress-Class CMS | GTAVI.PRO',
                description: 'Manage GTAVI.PRO block articles, live SEO audits, JSON-LD schema settings, and revision history.',
                canonicalUrl: $siteUrl.'/studio',
                robots: 'noindex, nofollow',
            ),
        ]);
    }

    public function create(SeoService $seo): View
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');

        return view('studio.editor', [
            'post' => null,
            'seoMeta' => $seo->buildPageMeta(
                title: 'New Dispatch — Vice Editorial Studio | GTAVI.PRO',
                description: 'Create a structured, high-SEO block dispatch inside GTAVI.PRO Editorial Studio.',
                canonicalUrl: $siteUrl.'/studio/create',
                robots: 'noindex, nofollow',
            ),
        ]);
    }

    public function edit(Post $post, SeoService $seo): View
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');

        return view('studio.editor', [
            'post' => $post->load(['category', 'tags', 'revisions']),
            'seoMeta' => $seo->buildPageMeta(
                title: 'Editing: '.$post->title.' — Vice Editorial Studio | GTAVI.PRO',
                description: 'Edit block layout, SEO metadata, and revisions in GTAVI.PRO Studio.',
                canonicalUrl: $siteUrl.'/studio/'.$post->id.'/edit',
                robots: 'noindex, nofollow',
            ),
        ]);
    }

    public function store(StorePostRequest $request, PostService $posts): RedirectResponse
    {
        $post = $posts->saveFromStudio(
            $request->validated(),
            null,
            $request->string('editor_note')->toString() ?: 'Created via API/HTTP Studio endpoint'
        );

        return redirect()
            ->route('studio.edit', $post)
            ->with('status', 'Dispatch saved and compiled with SEO telemetry.');
    }

    public function update(UpdatePostRequest $request, Post $post, PostService $posts): RedirectResponse
    {
        $updated = $posts->saveFromStudio(
            $request->validated(),
            $post,
            $request->string('editor_note')->toString() ?: 'Updated via Studio endpoint'
        );

        return redirect()
            ->route('studio.edit', $updated)
            ->with('status', 'Dispatch updated and revision snapshot stored.');
    }

    public function generateAi(GenerateAiPostRequest $request, GeminiArticleService $geminiArticleService): RedirectResponse
    {
        $validated = $request->validated();
        $mode = (string) $validated['mode'];
        $status = (string) $validated['status'];
        $rawTopic = trim((string) ($validated['topic'] ?? ''));
        $topic = $rawTopic !== '' ? $rawTopic : null;
        $modes = $mode === 'both' ? ['news', 'evergreen'] : [$mode];
        $createdPosts = [];

        try {
            foreach ($modes as $generationMode) {
                $createdPosts[] = $geminiArticleService->generateAndCreatePost(
                    mode: $generationMode,
                    topicOverride: $topic,
                    status: $status,
                );
            }
        } catch (Throwable $exception) {
            Log::error('Admin studio AI article generation failed.', [
                'mode' => $mode,
                'status' => $status,
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('studio.index')
                ->withInput()
                ->withErrors(['ai_generator' => $exception->getMessage()]);
        }

        if (count($createdPosts) === 1 && $status === 'draft') {
            $post = $createdPosts[0];
            $costLabel = $post->formattedAiCost() !== null ? ' • Est. Cost: '.$post->formattedAiCost() : '';

            return redirect()
                ->route('studio.edit', $post)
                ->with('status', sprintf(
                    'Gemini generated draft "%s" (%d words, %d/100 SEO, %s tokens%s). Review & publish when ready.',
                    $post->title,
                    $post->word_count,
                    $post->seo_score,
                    number_format($post->totalAiTokens()),
                    $costLabel,
                ));
        }

        if (count($createdPosts) === 1) {
            $post = $createdPosts[0];
            $costLabel = $post->formattedAiCost() !== null ? ' • Est. Cost: '.$post->formattedAiCost() : '';

            return redirect()
                ->route('studio.index')
                ->with('status', sprintf(
                    'Gemini generated & published "%s" (%d words, %d/100 SEO, %s tokens%s).',
                    $post->title,
                    $post->word_count,
                    $post->seo_score,
                    number_format($post->totalAiTokens()),
                    $costLabel,
                ));
        }

        $totalTokens = array_sum(array_map(static fn (Post $post): int => $post->totalAiTokens(), $createdPosts));
        $totalCostUsd = array_sum(array_map(static fn (Post $post): float => (float) ($post->ai_estimated_cost_usd ?? 0.0), $createdPosts));

        return redirect()
            ->route('studio.index')
            ->with('status', sprintf(
                'Gemini generated %d %s articles (%s tokens • $%s USD est.).',
                count($createdPosts),
                $status,
                number_format($totalTokens),
                number_format($totalCostUsd, 4),
            ));
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()
            ->route('studio.index')
            ->with('status', 'Dispatch deleted from GTAVI.PRO.');
    }
}
