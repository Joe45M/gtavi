@extends('layouts.app')

@section('content')
    @php
        $leadPost = $summary['featured_post'] ?? $summary['latest_posts']->first();
        $secondaryPosts = $summary['latest_posts']->when(
            $leadPost !== null,
            fn ($posts) => $posts->reject(fn ($item): bool => $item->id === $leadPost->id)
        );
    @endphp

    <!-- Clean White Editorial Lead Grid -->
    <section aria-labelledby="main-heading" class="grid gap-6 lg:grid-cols-12">
        <!-- Main Lead Story (8 cols) -->
        <div class="rounded-md border border-slate-200 border-t-2 border-t-[#be185d] bg-white p-6 shadow-xs sm:p-8 lg:col-span-8">
            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                <span class="rounded bg-[#fdf2f8] px-2.5 py-0.5 text-[#9d174d] border border-[#fbcfe8]">
                    Lead Report
                </span>
                @if ($leadPost)
                    <a href="{{ route('blog.category', $leadPost->category->slug) }}" class="rounded border border-purple-200 bg-purple-50 px-2.5 py-0.5 text-[#2b124c] hover:bg-purple-100">
                        {{ $leadPost->category->name }}
                    </a>
                    <span class="rounded border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-slate-600">
                        {{ $leadPost->verificationLabel() }}
                    </span>
                @endif
            </div>

            <h1 id="main-heading" tabindex="-1" class="mt-4 text-3xl font-bold leading-tight tracking-tight text-slate-900 sm:text-4xl">
                @if ($leadPost)
                    <a href="{{ route('blog.show', $leadPost->slug) }}" class="hover:text-[#2b124c]">
                        {{ $leadPost->title }}
                    </a>
                @else
                    GTA VI Release Info, Pricing &amp; Technical Telemetry
                @endif
            </h1>

            <p class="mt-4 text-base leading-relaxed text-slate-600 sm:text-lg">
                {{ $leadPost?->excerpt ?? 'Independent GTA VI release tracker, download time calculator, edition price matrix, and State of Leonida analysis—separate from official Rockstar Games announcements.' }}
            </p>

            @if ($leadPost)
                <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 border-y border-slate-200 py-3 text-xs text-slate-500">
                    <span>By <strong class="font-semibold text-slate-900">{{ $leadPost->author_name }}</strong> ({{ $leadPost->author_role }})</span>
                    <span>&bull; {{ ($leadPost->published_at ?? $leadPost->created_at)?->format('j M Y') }}</span>
                    <span>&bull; {{ $leadPost->reading_time_minutes }} min read</span>
                </div>
            @endif

            <!-- Calm "At a Glance" Summary Box -->
            <div class="mt-5 rounded border border-slate-200 border-l-3 border-l-[#2b124c] bg-[#f8f7fb] p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#2b124c]">
                    At a Glance &bull; Independent Desk Summary
                </p>
                <ul class="mt-2 space-y-1.5 text-sm text-slate-700">
                    <li>&bull; Pre-load footprint projections span <strong class="font-semibold text-slate-900">155–185 GB on console</strong> and up to <strong class="font-semibold text-slate-900">225 GB on PC</strong> with uncompressed 4K texture packs.</li>
                    <li>&bull; Standard retail tier is modeled around <strong class="font-semibold text-slate-900">$69.99 USD</strong> pending Rockstar Games / Take-Two Interactive storefront confirmation.</li>
                    <li>&bull; All forecasts on GTAVI.PRO are tagged separately from verified Take-Two Interactive earnings filings.</li>
                </ul>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                @if ($leadPost)
                    <a href="{{ route('blog.show', $leadPost->slug) }}" class="inline-flex min-h-[44px] items-center rounded bg-[#2b124c] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#3b1a66]">
                        Read Full Report &rarr;
                    </a>
                @endif
                <a href="{{ route('calculators') }}" class="inline-flex min-h-[44px] items-center rounded border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-800 transition hover:border-[#2b124c] hover:text-[#2b124c]">
                    Open Interactive Calculators
                </a>
                <a href="{{ route('blog.index') }}" class="inline-flex min-h-[44px] items-center rounded border border-transparent px-4 py-2.5 text-sm font-semibold text-[#be185d] transition hover:bg-[#fdf2f8]">
                    All News &amp; Features
                </a>
            </div>
        </div>

        <!-- Right Briefing Column: Key Figures + Headlines Wire (4 cols) -->
        <aside aria-label="Key Figures and Latest Headlines" class="flex flex-col gap-6 lg:col-span-4">
            <!-- Key Figures Panel -->
            <div class="rounded-md border border-slate-200 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                    <h2 class="text-sm font-bold text-slate-900">Key GTA VI Figures</h2>
                    <span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                        Projected / Tracked
                    </span>
                </div>

                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded border border-slate-200 bg-[#f8f7fb] p-3">
                        <dt class="text-xs font-medium text-slate-500">Base Console Size</dt>
                        <dd class="mt-1 text-lg font-bold text-slate-900">155–185 GB</dd>
                    </div>
                    <div class="rounded border border-slate-200 bg-[#f8f7fb] p-3">
                        <dt class="text-xs font-medium text-slate-500">Standard Target</dt>
                        <dd class="mt-1 text-lg font-bold text-slate-900">$69.99 USD</dd>
                    </div>
                    <div class="rounded border border-slate-200 bg-[#f8f7fb] p-3">
                        <dt class="text-xs font-medium text-slate-500">Leonida Scale</dt>
                        <dd class="mt-1 text-lg font-bold text-slate-900">~{{ number_format($summary['stats']['total_leonida_area'], 1) }} km²</dd>
                    </div>
                    <div class="rounded border border-slate-200 bg-[#f8f7fb] p-3">
                        <dt class="text-xs font-medium text-slate-500">Hardware Profiles</dt>
                        <dd class="mt-1 text-lg font-bold text-[#2b124c]">{{ $summary['stats']['platforms_tracked'] }} Tracked</dd>
                    </div>
                </dl>
            </div>

            <!-- Wire Headlines -->
            <div class="flex-1 rounded-md border border-slate-200 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                    <h2 class="text-sm font-bold text-slate-900">Latest News Wire</h2>
                    <a href="{{ route('blog.index') }}" class="text-xs font-semibold text-[#be185d] hover:text-[#2b124c]">
                        More &rarr;
                    </a>
                </div>

                <ul class="mt-3 divide-y divide-slate-200">
                    @foreach ($secondaryPosts->take(4) as $wirePost)
                        <li class="py-3 first:pt-1 last:pb-0">
                            <span class="block text-[11px] font-semibold text-[#be185d]">
                                {{ $wirePost->category->name }} &bull; {{ ($wirePost->published_at ?? $wirePost->created_at)?->format('j M') }}
                            </span>
                            <h3 class="mt-1 text-sm font-semibold leading-snug text-slate-900">
                                <a href="{{ route('blog.show', $wirePost->slug) }}" class="hover:text-[#2b124c]">
                                    {{ $wirePost->title }}
                                </a>
                            </h3>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>
    </section>

    <!-- Reference Desks Strip -->
    <section aria-labelledby="hubs-heading" class="mt-10 border-t border-slate-200 pt-8">
        <div class="flex flex-wrap items-baseline justify-between gap-4">
            <div>
                <h2 id="hubs-heading" class="text-xl font-bold text-slate-900 sm:text-2xl">Specialized Reference Desks</h2>
                <p class="mt-1 text-sm text-slate-600">Dedicated data trackers separating confirmed investor filings from independent estimates.</p>
            </div>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('release-info') }}" class="group rounded-md border border-slate-200 bg-white p-5 shadow-xs transition hover:border-[#2b124c]">
                <span class="text-xs font-semibold text-[#be185d]">01 &bull; Timeline</span>
                <h3 class="mt-1.5 text-base font-bold text-slate-900 group-hover:text-[#2b124c]">Release Info &amp; Filings</h3>
                <p class="mt-1.5 text-sm text-slate-600">Rockstar Newswire history, Take-Two earnings guidance, and console-to-PC gaps.</p>
            </a>

            <a href="{{ route('prices-editions') }}" class="group rounded-md border border-slate-200 bg-white p-5 shadow-xs transition hover:border-[#2b124c]">
                <span class="text-xs font-semibold text-[#2b124c]">02 &bull; Pricing</span>
                <h3 class="mt-1.5 text-base font-bold text-slate-900 group-hover:text-[#2b124c]">Prices &amp; Editions</h3>
                <p class="mt-1.5 text-sm text-slate-600">Standard, Deluxe, and Collector projections across USD, GBP, EUR, CAD, AUD &amp; JPY.</p>
            </a>

            <a href="{{ route('download-sizes') }}" class="group rounded-md border border-slate-200 bg-white p-5 shadow-xs transition hover:border-[#2b124c]">
                <span class="text-xs font-semibold text-[#be185d]">03 &bull; Hardware</span>
                <h3 class="mt-1.5 text-base font-bold text-slate-900 group-hover:text-[#2b124c]">Download Sizes &amp; Specs</h3>
                <p class="mt-1.5 text-sm text-slate-600">PS5, PS5 Pro PSSR, Series X|S, and PC DirectStorage NVMe benchmarks.</p>
            </a>

            <a href="{{ route('map-atlas') }}" class="group rounded-md border border-slate-200 bg-white p-5 shadow-xs transition hover:border-[#2b124c]">
                <span class="text-xs font-semibold text-[#2b124c]">04 &bull; Geography</span>
                <h3 class="mt-1.5 text-base font-bold text-slate-900 group-hover:text-[#2b124c]">Leonida Map Atlas</h3>
                <p class="mt-1.5 text-sm text-slate-600">Vice City Metro, Grassrivers, Leonida Keys, Port Gellhorn, and Ambrosia.</p>
            </a>
        </div>
    </section>

    <!-- Live Interactive Calculator Suite -->
    <section aria-labelledby="calc-suite-heading" class="mt-12 border-t border-slate-200 pt-8">
        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-4">
            <div>
                <h2 id="calc-suite-heading" class="text-xl font-bold text-slate-900 sm:text-2xl">Interactive GTA VI Calculators</h2>
                <p class="mt-1 text-sm text-slate-600">Estimate download durations, PC FPS headroom, regional edition value, and Leonida traversal time.</p>
            </div>
            <a href="{{ route('calculators') }}" class="text-sm font-semibold text-[#be185d] hover:text-[#2b124c]">
                Open Full-Page Toolkit &rarr;
            </a>
        </div>

        <livewire:interactive-calculators />
    </section>

    <!-- News & Special Reports Grid -->
    <section aria-labelledby="latest-blog-heading" class="mt-12 border-t border-slate-200 pt-8">
        <div class="mb-6 flex flex-wrap items-baseline justify-between gap-4">
            <div>
                <h2 id="latest-blog-heading" class="text-xl font-bold text-slate-900 sm:text-2xl">More Reports &amp; Deep Dives</h2>
                <p class="mt-1 text-sm text-slate-600">In-depth reporting on storage math, RAGE 9 architecture, pricing tiers, and PC specs.</p>
            </div>
            <a href="{{ route('blog.index') }}" class="text-sm font-semibold text-[#be185d] hover:text-[#2b124c]">
                Browse All {{ $summary['stats']['published_articles'] }} Articles &rarr;
            </a>
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($summary['latest_posts'] as $post)
                <article class="article-list-item flex flex-col rounded-md border border-slate-200 bg-white p-5 shadow-xs transition hover:border-slate-300">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs font-semibold">
                        <a href="{{ route('blog.category', $post->category->slug) }}" class="text-[#be185d] hover:text-[#2b124c]">
                            {{ $post->category->name }}
                        </a>
                        <span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                            {{ $post->verificationLabel() }}
                        </span>
                    </div>

                    <h3 class="mt-3 text-lg font-bold leading-snug text-slate-900">
                        <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-[#2b124c]">
                            {{ $post->title }}
                        </a>
                    </h3>

                    <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-600">
                        {{ $post->excerpt }}
                    </p>

                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-500">
                        <span>{{ ($post->published_at ?? $post->created_at)?->format('j M Y') }}</span>
                        <span>{{ $post->reading_time_minutes }} min read &bull; {{ $post->word_count }} words</span>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <!-- Editions Snapshot Table -->
    <section aria-labelledby="editions-preview-heading" class="mt-12 border-t border-slate-200 pt-8">
        <div class="mb-4 flex flex-wrap items-baseline justify-between gap-4">
            <div>
                <h2 id="editions-preview-heading" class="text-xl font-bold text-slate-900 sm:text-2xl">Projected Editions &amp; Global Pricing Snapshot</h2>
                <p class="mt-1 text-sm text-slate-600">Unannounced SKU tiers reflect independent market projections rather than official Rockstar pricing.</p>
            </div>
            <a href="{{ route('prices-editions') }}" class="text-sm font-semibold text-[#be185d] hover:text-[#2b124c]">
                Full Edition Breakdown &rarr;
            </a>
        </div>

        <div tabindex="0" role="region" aria-labelledby="home-editions-caption" class="table-wrapper">
            <table class="vice-table" style='--label-1: "Edition"; --label-2: "USD"; --label-3: "GBP"; --label-4: "EUR"; --label-5: "Est. Size"; --label-6: "Status";'>
                <caption id="home-editions-caption">Projected GTA VI SKU Pricing Comparison (Unofficial)</caption>
                <thead>
                    <tr>
                        <th scope="col">Edition</th>
                        <th scope="col">USD ($)</th>
                        <th scope="col">GBP (£)</th>
                        <th scope="col">EUR (€)</th>
                        <th scope="col">Est. Size</th>
                        <th scope="col">Confirmation Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($summary['editions'] as $edition)
                        <tr>
                            <th scope="row">{{ $edition->name }}</th>
                            <td class="font-bold text-slate-900">${{ number_format($edition->price_usd, 2) }}</td>
                            <td>£{{ number_format($edition->price_gbp, 2) }}</td>
                            <td>€{{ number_format($edition->price_eur, 2) }}</td>
                            <td>{{ $edition->estimated_size_gb }} GB</td>
                            <td>
                                <span class="inline-flex rounded px-2 py-0.5 text-xs font-semibold {{ $edition->is_price_confirmed ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-purple-50 text-[#2b124c] border border-purple-200' }}">
                                    {{ $edition->is_price_confirmed ? 'Official MSRP' : 'Projected Estimate' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
