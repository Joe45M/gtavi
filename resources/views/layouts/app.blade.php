@php
    $meta = $seoMeta ?? [
        'title' => 'GTAVI.PRO — Independent GTA VI News, Release Data & Calculators',
        'description' => 'Independent fan newsroom and hardware telemetry desk for gtavi.pro. Not affiliated with Rockstar Games or Take-Two Interactive.',
        'canonical' => url()->current(),
        'og_type' => 'website',
        'og_title' => 'GTAVI.PRO — GTA VI News & Telemetry Desk',
        'og_description' => 'Independent GTA VI news & calculators. Not affiliated with Rockstar Games or Take-Two Interactive.',
        'robots' => 'index, follow',
        'json_ld' => [],
    ];
    $routeName = request()->route()?->getName() ?? '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta['title'] }}</title>
    <meta name="description" content="{{ $meta['description'] }}">
    <meta name="robots" content="{{ $meta['robots'] ?? 'index, follow' }}">
    <link rel="canonical" href="{{ $meta['canonical'] }}">

    <!-- OpenGraph -->
    <meta property="og:site_name" content="GTAVI.PRO">
    <meta property="og:type" content="{{ $meta['og_type'] ?? 'website' }}">
    <meta property="og:title" content="{{ $meta['og_title'] ?? $meta['title'] }}">
    <meta property="og:description" content="{{ $meta['og_description'] ?? $meta['description'] }}">
    <meta property="og:url" content="{{ $meta['canonical'] }}">
    <meta property="og:locale" content="en_US">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $meta['og_title'] ?? $meta['title'] }}">
    <meta name="twitter:description" content="{{ $meta['og_description'] ?? $meta['description'] }}">

    @if (!empty($meta['json_ld']))
        <script type="application/ld+json">{!! json_encode($meta['json_ld'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#f6f5f9] font-sans text-[#2d263e] antialiased selection:bg-[#2b124c] selection:text-white">
    <a href="#main-heading" class="skip-link visually-hidden">Skip to main content</a>

    <!-- Non-Affiliation & Edition Utility Bar -->
    <div class="border-b border-[#341c54] bg-[#180b2b] px-4 py-1.5 text-xs text-purple-100/85">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-2">
            <p class="flex flex-wrap items-center gap-2 leading-snug">
                <span class="inline-flex shrink-0 items-center rounded bg-white/10 px-2 py-0.5 text-[11px] font-semibold text-white">
                    Independent Publication
                </span>
                <span>
                    <strong class="font-semibold text-white">GTAVI.PRO</strong> is an unofficial fan news &amp; analysis desk.
                    <span class="text-purple-200">Not affiliated with Rockstar Games or Take-Two Interactive.</span>
                </span>
            </p>
            <div class="flex items-center gap-4">
                <span class="hidden text-[11px] text-purple-200/80 sm:inline">
                    {{ now()->format('l, j F Y') }}
                </span>
                <button
                    type="button"
                    command="show-modal"
                    commandfor="legal-disclaimer-dialog"
                    class="cursor-pointer rounded border border-white/15 bg-white/10 px-2.5 py-0.5 text-[11px] font-medium text-white transition hover:bg-white/20"
                >
                    Legal Notice
                </button>
                <a href="{{ route('disclaimer') }}" class="text-[11px] font-medium text-purple-200 underline decoration-purple-300/50 underline-offset-2 hover:text-white">
                    Verification Policy
                </a>
            </div>
        </div>
    </div>

    <!-- Broadcast News Masthead & White Section Navigation -->
    <header class="sticky top-0 z-40 shadow-xs">
        <div class="bg-[#24103f] text-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 no-underline">
                    <span class="rounded-sm border-b-2 border-[#be185d] bg-[#331858] px-2.5 py-1 text-base font-bold tracking-tight text-white sm:text-lg">
                        GTA VI
                    </span>
                    <span class="text-lg font-bold tracking-tight text-white sm:text-xl">
                        .PRO
                    </span>
                    <span class="hidden border-l border-white/20 pl-3 text-xs font-medium text-purple-200 sm:inline">
                        News &amp; Analysis Desk
                    </span>
                </a>

                <!-- Mobile Popover Trigger -->
                <button
                    type="button"
                    popovertarget="site-mobile-menu"
                    popovertargetaction="toggle"
                    aria-controls="site-mobile-menu"
                    class="inline-flex min-h-[44px] cursor-pointer items-center gap-2 rounded border border-white/20 bg-white/10 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-white/15 lg:hidden"
                >
                    <svg aria-hidden="true" class="h-5 w-5 text-purple-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span>Sections</span>
                </button>

                <p class="hidden text-xs font-medium text-purple-200/90 lg:block">
                    Confirmed Filings &bull; Editorial Analysis &bull; Interactive Calculators
                </p>
            </div>
        </div>

        <!-- Crisp White Secondary Navigation Strip -->
        <nav aria-label="Primary" class="border-b border-slate-200 bg-white">
            @php
                $navItems = [
                    ['route' => 'home', 'label' => 'Top Stories', 'active' => $routeName === 'home'],
                    ['route' => 'release-info', 'label' => 'Release Info', 'active' => $routeName === 'release-info'],
                    ['route' => 'prices-editions', 'label' => 'Prices & Editions', 'active' => $routeName === 'prices-editions'],
                    ['route' => 'download-sizes', 'label' => 'Download & Specs', 'active' => $routeName === 'download-sizes'],
                    ['route' => 'calculators', 'label' => 'Calculators', 'active' => $routeName === 'calculators'],
                    ['route' => 'map-atlas', 'label' => 'Leonida Atlas', 'active' => $routeName === 'map-atlas'],
                    ['route' => 'blog.index', 'label' => 'News & Features', 'active' => str_starts_with($routeName, 'blog.')],
                ];
            @endphp

            <ul class="mx-auto hidden max-w-7xl items-stretch px-4 sm:px-6 lg:flex">
                @foreach ($navItems as $item)
                    <li>
                        <a
                            href="{{ route($item['route']) }}"
                            @if ($item['active']) aria-current="page" @endif
                            class="inline-flex min-h-[44px] items-center border-b-2 px-4 py-2 text-sm transition {{ $item['active'] ? 'border-[#be185d] bg-[#faf9fc] font-semibold text-[#2b124c]' : 'border-transparent font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <!-- Mobile Popover Navigation Drawer -->
            <ul
                id="site-mobile-menu"
                popover="auto"
                class="fixed inset-x-4 top-24 z-50 mx-auto max-w-md rounded-md border border-slate-200 border-t-2 border-t-[#be185d] bg-white p-4 text-slate-900 shadow-xl"
            >
                <li class="mb-2 border-b border-slate-200 pb-2 text-xs font-semibold tracking-wide text-[#2b124c]">
                    GTAVI.PRO Sections
                </li>
                @foreach ($navItems as $item)
                    <li>
                        <a
                            href="{{ route($item['route']) }}"
                            @if ($item['active']) aria-current="page" @endif
                            class="flex min-h-[44px] items-center rounded border-l-2 px-3 py-2 text-sm transition {{ $item['active'] ? 'border-[#be185d] bg-[#f8f7fb] font-semibold text-[#2b124c]' : 'border-transparent font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </header>

    <main class="mx-auto min-h-[70dvh] w-full max-w-7xl px-4 py-6 sm:px-6 lg:py-8">
        @yield('content')
    </main>

    <!-- Persistent Editorial Footer -->
    <footer class="mt-16 border-t border-slate-200 bg-[#180b2b] text-slate-300">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
            <div class="mb-8 rounded-md border border-[#3a205d] border-l-3 border-l-[#be185d] bg-[#22113a] p-5">
                <p class="text-xs font-semibold tracking-wide text-pink-200">
                    Mandatory Independence &amp; Trademark Disclaimer
                </p>
                <p class="mt-2 text-sm leading-relaxed text-slate-200">
                    <strong class="font-semibold text-white">GTAVI.PRO</strong> is an independent fan newsroom, technical analysis resource, and editorial publication.
                    <strong class="font-semibold text-white">This website is NOT affiliated with, endorsed by, sponsored by, or connected to Rockstar Games, Take-Two Interactive Software, Inc., or any of their subsidiaries.</strong>
                    Grand Theft Auto, Grand Theft Auto VI, GTA VI, Vice City, Leonida, and RAGE are registered trademarks or trademarks of Take-Two Interactive Software, Inc.
                    Unannounced pricing, storage footprints, and PC specifications are clearly labeled projections separate from verified filings.
                </p>
            </div>

            <div class="grid gap-8 border-b border-[#341c54] pb-8 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-base font-bold text-white">
                        <span class="rounded-sm border-b-2 border-[#be185d] bg-[#331858] px-2 py-0.5 text-sm">GTA VI</span> .PRO
                    </p>
                    <p class="mt-2 text-sm text-purple-200/80">
                        Independent reporting, release milestone tracking, and hardware telemetry tools built on Laravel &amp; Livewire.
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-purple-200">Reference Desks</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="{{ route('release-info') }}" class="text-slate-300 hover:text-white">Release Info &amp; Filings</a></li>
                        <li><a href="{{ route('prices-editions') }}" class="text-slate-300 hover:text-white">Prices &amp; Editions Matrix</a></li>
                        <li><a href="{{ route('download-sizes') }}" class="text-slate-300 hover:text-white">Download Sizes &amp; SSD Specs</a></li>
                        <li><a href="{{ route('map-atlas') }}" class="text-slate-300 hover:text-white">State of Leonida Map Atlas</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-purple-200">Tools &amp; Newsroom</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="{{ route('calculators', ['tab' => 'download']) }}" class="text-slate-300 hover:text-white">Download Time Calculator</a></li>
                        <li><a href="{{ route('calculators', ['tab' => 'pc']) }}" class="text-slate-300 hover:text-white">PC Hardware Readiness</a></li>
                        <li><a href="{{ route('calculators', ['tab' => 'edition']) }}" class="text-slate-300 hover:text-white">Edition Value Calculator</a></li>
                        <li><a href="{{ route('blog.index') }}" class="text-slate-300 hover:text-white">News &amp; Feature Articles</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-purple-200">Standards &amp; Crawlers</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="{{ route('disclaimer') }}" class="text-slate-300 hover:text-white">Legal Non-Affiliation Notice</a></li>
                        <li><a href="{{ route('seo.sitemap') }}" class="text-slate-300 hover:text-white">XML Sitemap (/sitemap.xml)</a></li>
                        <li><a href="{{ route('seo.robots') }}" class="text-slate-300 hover:text-white">Robots Policy (/robots.txt)</a></li>
                        <li><a href="{{ route('seo.llms') }}" class="text-slate-300 hover:text-white">LLMs Index (/llms.txt)</a></li>
                        <li><a href="{{ route('api.v1.posts.index') }}" class="text-slate-300 hover:text-white">JSON API v1 (/api/v1/posts)</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4 text-xs text-purple-200/75">
                <p>&copy; {{ date('Y') }} GTAVI.PRO — Independent Community Publication. Zero affiliation with Rockstar Games or Take-Two Interactive.</p>
                <a href="{{ route('studio.index') }}" class="font-medium text-purple-200 hover:text-white">
                    Admin Studio
                </a>
            </div>
        </div>
    </footer>

    <!-- Native Modal Dialog for Non-Affiliation Notice -->
    <dialog id="legal-disclaimer-dialog" closedby="any" aria-labelledby="legal-dialog-title" class="vice-dialog">
        <div class="p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-4">
                <div>
                    <span class="inline-flex rounded bg-[#fdf2f8] px-2.5 py-0.5 text-xs font-semibold text-[#9d174d] border border-[#fbcfe8]">
                        Independence Disclosure
                    </span>
                    <h2 id="legal-dialog-title" class="mt-2 text-xl font-bold text-slate-900">
                        GTAVI.PRO Legal Non-Affiliation Notice
                    </h2>
                </div>
                <form method="dialog">
                    <button
                        type="submit"
                        autofocus
                        class="min-h-[44px] min-w-[44px] cursor-pointer rounded border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Close
                    </button>
                </form>
            </div>

            <div class="mt-4 space-y-3 text-sm leading-relaxed text-slate-700">
                <p>
                    <strong class="font-semibold text-slate-900">GTAVI.PRO</strong> is an independently owned domain and technical fan publication covering open-world hardware requirements, storage projections, regional pricing models, and publicly verified announcements.
                </p>
                <p>
                    We are <strong class="font-semibold text-slate-900">not affiliated with, associated with, authorized by, endorsed by, or in any way officially connected with Rockstar Games, Rockstar North, or Take-Two Interactive Software, Inc.</strong>
                </p>
                <p>
                    All official news, trailers, pre-orders, and release dates must always be verified directly through Rockstar Games Newswire and Take-Two Interactive Investor Relations.
                </p>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-4">
                <a href="{{ route('disclaimer') }}" class="inline-flex min-h-[44px] items-center rounded border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Read Full Legal Policy
                </a>
                <form method="dialog">
                    <button type="submit" class="inline-flex min-h-[44px] cursor-pointer items-center rounded bg-[#2b124c] px-5 py-2 text-sm font-semibold text-white hover:bg-[#3b1a66]">
                        Understood
                    </button>
                </form>
            </div>
        </div>
    </dialog>

    @livewireScripts
</body>
</html>
