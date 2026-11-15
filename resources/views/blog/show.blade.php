@extends('layouts.app')

@section('content')
    <!-- Breadcrumbs -->
    <nav aria-label="Breadcrumb" class="mb-6 text-xs font-medium text-slate-500">
        <ol class="flex flex-wrap items-center gap-2">
            <li><a href="{{ route('home') }}" class="hover:text-[#2b124c]">Home</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="{{ route('blog.index') }}" class="hover:text-[#2b124c]">News &amp; Features</a></li>
            <li aria-hidden="true">/</li>
            <li>
                <a href="{{ route('blog.category', $post->category->slug) }}" class="hover:text-[#2b124c]">
                    {{ $post->category->name }}
                </a>
            </li>
            <li aria-hidden="true">/</li>
            <li aria-current="page" class="font-semibold text-[#2b124c]">{{ \Illuminate\Support\Str::limit($post->title, 52) }}</li>
        </ol>
    </nav>

    <div class="grid gap-10 lg:grid-cols-12">
        <!-- Main Article Column -->
        <article class="rounded-md border border-slate-200 bg-white p-6 shadow-xs sm:p-10 lg:col-span-8">
            <header class="border-b border-slate-200 pb-6">
                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                    <a href="{{ route('blog.category', $post->category->slug) }}" class="rounded border border-[#fbcfe8] bg-[#fdf2f8] px-2.5 py-0.5 text-[#9d174d] hover:bg-pink-100">
                        {{ $post->category->name }}
                    </a>
                    <span class="rounded border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-slate-600">
                        {{ $post->verificationLabel() }}
                    </span>
                </div>

                <h1 id="main-heading" tabindex="-1" class="mt-4 text-3xl font-bold leading-tight tracking-tight text-slate-900 sm:text-4xl">
                    {{ $post->title }}
                </h1>

                @if ($post->subtitle)
                    <p class="article-lead-summary mt-3 text-lg leading-relaxed text-slate-600">{{ $post->subtitle }}</p>
                @else
                    <p class="article-lead-summary mt-3 text-lg leading-relaxed text-slate-600">{{ $post->excerpt }}</p>
                @endif

                @php
                    $publishedDate = $post->published_at ?? $post->created_at;
                    $updatedDate = $post->updated_at ?? $publishedDate;
                @endphp

                <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 pt-4 text-xs text-slate-500">
                    <div>
                        <span class="font-semibold text-slate-900">{{ $post->author_name }}</span>
                        <span>&bull; {{ $post->author_role }}</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($publishedDate)
                            <span>Published <time datetime="{{ $publishedDate->toIso8601String() }}">{{ $publishedDate->format('j M Y') }}</time></span>
                        @endif
                        @if ($updatedDate)
                            <span>&bull; Updated <time datetime="{{ $updatedDate->toIso8601String() }}">{{ $updatedDate->format('j M Y') }}</time></span>
                        @endif
                        <span>&bull; {{ $post->word_count }} words</span>
                        <span>&bull; {{ $post->reading_time_minutes }} min read</span>
                    </div>
                </div>
            </header>

            <div class="article-prose mt-8">
                {!! $renderedHtml !!}
            </div>

            <!-- Tags -->
            @if ($post->tags->isNotEmpty())
                <div class="mt-10 border-t border-slate-200 pt-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Topics Covered</p>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($post->tags as $tag)
                            <li>
                                <a href="{{ route('blog.tag', $tag->slug) }}" class="inline-flex min-h-[36px] items-center rounded border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-700 hover:border-[#2b124c] hover:text-[#2b124c]">
                                    #{{ $tag->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Explicit Article Non-Affiliation Box -->
            <aside aria-label="Editorial Independence Disclaimer" class="mt-8 rounded-md border border-[#fbcfe8] border-l-3 border-l-[#be185d] bg-[#fdf2f8] p-5 text-sm text-slate-800">
                <strong class="font-semibold text-[#9d174d]">Independence Notice:</strong> This report on GTAVI.PRO is an independent technical analysis. GTAVI.PRO is not affiliated with Rockstar Games or Take-Two Interactive.
            </aside>
        </article>

        <!-- Sticky Editorial Sidebar -->
        <aside aria-label="Article Navigation and Fact Sheet" class="space-y-6 lg:col-span-4">
            <div class="sticky top-28 space-y-6">
                @if (!empty($toc))
                    <div class="rounded-md border border-slate-200 border-t-2 border-t-[#2b124c] bg-white p-5 shadow-xs">
                        <p class="text-xs font-semibold uppercase tracking-wide text-[#2b124c]">
                            In This Report
                        </p>
                        <nav aria-label="Table of contents" class="mt-3">
                            <ul class="space-y-1.5 text-sm">
                                @foreach ($toc as $entry)
                                    <li class="{{ $entry['level'] === 3 ? 'ps-4 text-xs' : '' }}">
                                        <a href="#{{ $entry['id'] }}" class="block py-1 text-slate-600 hover:text-[#be185d]">
                                            {{ $entry['text'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </nav>
                    </div>
                @endif

                <div class="rounded-md border border-slate-200 bg-white p-5 shadow-xs">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Dispatch Fact Sheet
                    </p>
                    <dl class="mt-3 space-y-2.5 border-t border-slate-100 pt-3 text-xs">
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Section</dt>
                            <dd class="font-semibold text-slate-900">{{ $post->category->name }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Classification</dt>
                            <dd class="font-semibold text-[#be185d]">{{ $post->verificationLabel() }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Published</dt>
                            <dd class="font-semibold text-slate-900">
                                @if ($publishedDate)
                                    <time datetime="{{ $publishedDate->toIso8601String() }}">{{ $publishedDate->format('j M Y') }}</time>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Last Verified</dt>
                            <dd class="font-semibold text-slate-900">
                                @if ($updatedDate)
                                    <time datetime="{{ $updatedDate->toIso8601String() }}">{{ $updatedDate->format('j M Y') }}</time>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Length</dt>
                            <dd class="font-semibold text-slate-900">{{ $post->word_count }} words ({{ $post->reading_time_minutes }} min)</dd>
                        </div>
                    </dl>

                    <div class="mt-4 border-t border-slate-100 pt-4 text-xs">
                        <p class="font-semibold uppercase tracking-wide text-[#2b124c]">Citation &amp; Machine Formats</p>
                        <ul class="mt-2 space-y-1.5">
                            <li><a href="{{ route('blog.show.markdown', $post->slug) }}" class="font-medium text-[#2b124c] hover:text-[#be185d]">&rarr; View Clean Markdown (.md)</a></li>
                            <li><a href="{{ route('calculators', ['tab' => 'download']) }}" class="text-slate-600 hover:text-[#be185d]">&rarr; Download Time Calculator</a></li>
                            <li><a href="{{ route('prices-editions') }}" class="text-slate-600 hover:text-[#be185d]">&rarr; Editions &amp; Pricing Matrix</a></li>
                            <li><a href="{{ route('release-info') }}" class="text-slate-600 hover:text-[#be185d]">&rarr; Verified Filings Timeline</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <!-- Related Dispatches -->
    @if ($relatedPosts->isNotEmpty())
        <section aria-labelledby="related-heading" class="mt-14 border-t border-slate-200 pt-8">
            <h2 id="related-heading" class="text-xl font-bold text-slate-900 sm:text-2xl">Related GTAVI.PRO Reports</h2>
            <div class="mt-6 grid gap-6 md:grid-cols-3">
                @foreach ($relatedPosts as $rel)
                    <article class="article-list-item rounded-md border border-slate-200 bg-white p-5 shadow-xs">
                        <span class="text-xs font-semibold text-[#be185d]">{{ $rel->category->name }}</span>
                        <h3 class="mt-2 text-base font-bold text-slate-900">
                            <a href="{{ route('blog.show', $rel->slug) }}" class="hover:text-[#2b124c]">{{ $rel->title }}</a>
                        </h3>
                        <p class="mt-2 text-xs leading-relaxed text-slate-600">{{ \Illuminate\Support\Str::limit($rel->excerpt, 110) }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
