@extends('layouts.admin')

@section('content')
    @php
        $totalCount = $posts->total();
        $publishedOnPage = $posts->getCollection()->where('status', 'published')->count();
        $avgSeoOnPage = $posts->isNotEmpty() ? (int) round((float) $posts->getCollection()->avg('seo_score')) : 0;
        $avgReadOnPage = $posts->isNotEmpty() ? (int) round((float) $posts->getCollection()->avg('readability_score')) : 0;
    @endphp

    <header class="rounded-md border border-slate-200 bg-white p-6 shadow-xs sm:p-7">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <span class="inline-flex rounded border border-[#fbcfe8] bg-[#fdf2f8] px-2.5 py-0.5 text-xs font-semibold text-[#9d174d]">
                    Back-Office Admin Panel &bull; SEO Telemetry Enabled
                </span>
                <h1 id="main-heading" tabindex="-1" class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Editorial CMS &amp; SEO Audit Console
                </h1>
                <p class="mt-1.5 max-w-2xl text-sm text-slate-600">
                    Manage structured block articles, internal Yoast-grade SEO scores, readability checks, JSON-LD schema types, and revision snapshots.
                </p>
            </div>

            <a href="{{ route('studio.create') }}" class="inline-flex min-h-[44px] items-center rounded bg-[#2b124c] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#3b1a66]">
                + Compose New Article
            </a>
        </div>
    </header>

    <!-- Admin KPI Summary Strip -->
    <section aria-label="Editorial Admin Metrics" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-md border border-slate-200 border-l-3 border-l-[#be185d] bg-white p-4 shadow-xs">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Managed Articles</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $totalCount }}</p>
        </div>
        <div class="rounded-md border border-slate-200 border-l-3 border-l-[#2b124c] bg-white p-4 shadow-xs">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Published (This View)</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $publishedOnPage }}</p>
        </div>
        <div class="rounded-md border border-slate-200 border-l-3 border-l-emerald-600 bg-white p-4 shadow-xs">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Admin Avg SEO Score</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700">{{ $avgSeoOnPage }}/100</p>
        </div>
        <div class="rounded-md border border-slate-200 border-l-3 border-l-purple-600 bg-white p-4 shadow-xs">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Admin Avg Readability</p>
            <p class="mt-1 text-2xl font-bold text-[#2b124c]">{{ $avgReadOnPage }}/100</p>
        </div>
    </section>

    @if (session('status'))
        <div role="status" class="mt-6 rounded-md border border-emerald-200 border-l-3 border-l-emerald-600 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <section aria-label="Managed Dispatches" class="mt-6">
        <div tabindex="0" role="region" aria-labelledby="studio-posts-caption" class="table-wrapper">
            <table class="vice-table" style='--label-1: "Title"; --label-2: "Category"; --label-3: "Status"; --label-4: "Admin SEO / Read"; --label-5: "Revisions"; --label-6: "Actions";'>
                <caption id="studio-posts-caption">All GTAVI.PRO Editorial Dispatches ({{ $posts->total() }})</caption>
                <thead>
                    <tr>
                        <th scope="col">Title &amp; Focus Keyword</th>
                        <th scope="col">Category</th>
                        <th scope="col">Status</th>
                        <th scope="col">Admin SEO / Readability</th>
                        <th scope="col">Revisions</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($posts as $post)
                        <tr>
                            <th scope="row">
                                <a href="{{ route('studio.edit', $post) }}" class="text-slate-900 hover:text-[#be185d]">
                                    {{ $post->title }}
                                </a>
                                <span class="mt-1 block font-mono text-xs font-normal text-slate-500">
                                    /{{ $post->slug }} &bull; KW: {{ $post->focus_keyword ?: 'unset' }}
                                </span>
                            </th>
                            <td>{{ $post->category->name }}</td>
                            <td>
                                <span class="inline-flex rounded px-2 py-0.5 text-xs font-semibold uppercase {{ $post->status === 'published' ? 'border border-emerald-200 bg-emerald-50 text-emerald-800' : 'border border-amber-200 bg-amber-50 text-amber-800' }}">
                                    {{ $post->status }}
                                </span>
                            </td>
                            <td>
                                <span class="font-bold text-emerald-700">{{ $post->seo_score }}/100 SEO</span>
                                <span class="block text-xs text-slate-500">{{ $post->readability_score }}/100 Read &bull; {{ $post->word_count }}w</span>
                                @if ($post->formattedAiCost() !== null)
                                    <span class="mt-0.5 block font-mono text-[11px] text-[#2b124c]">
                                        AI: {{ $post->formattedAiCost() }} &bull; {{ number_format($post->totalAiTokens()) }} tok
                                    </span>
                                @endif
                            </td>
                            <td>{{ $post->revisions_count }}</td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('studio.edit', $post) }}" class="rounded bg-[#2b124c] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#3b1a66]">
                                        Edit Blocks
                                    </a>
                                    @if ($post->status === 'published')
                                        <a href="{{ route('blog.show', $post->slug) }}" class="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-[#2b124c] hover:text-[#2b124c]">
                                            View Live
                                        </a>
                                    @endif
                                    @can('delete posts')
                                        <form method="POST" action="{{ route('studio.destroy', $post) }}" data-confirm="Delete this dispatch permanently?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="cursor-pointer rounded border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-600 hover:text-white">
                                                Delete
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $posts->links() }}
        </div>
    </section>
@endsection
