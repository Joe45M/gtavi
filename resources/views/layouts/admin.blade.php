@php
    $meta = $seoMeta ?? [
        'title' => 'Admin Panel — Editorial & SEO Studio | GTAVI.PRO',
        'description' => 'Internal Admin Panel and CMS Studio for GTAVI.PRO. Not affiliated with Rockstar Games or Take-Two Interactive.',
        'canonical' => url()->current(),
        'robots' => 'noindex, nofollow',
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
    <meta name="robots" content="{{ $meta['robots'] ?? 'noindex, nofollow' }}">
    <link rel="canonical" href="{{ $meta['canonical'] }}">

    @if (!empty($meta['json_ld']))
        <script type="application/ld+json">{!! json_encode($meta['json_ld'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#f6f5f9] font-sans text-[#2d263e] antialiased selection:bg-[#2b124c] selection:text-white">
    <a href="#main-heading" class="skip-link visually-hidden">Skip to admin workspace</a>

    <!-- Internal Admin Panel Header -->
    <header class="sticky top-0 z-40 shadow-xs">
        <div class="border-b border-[#341c54] bg-[#180b2b] px-4 py-1.5 text-[11px] text-purple-100/85">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-2">
                <p class="flex items-center gap-2 font-medium">
                    <span class="rounded bg-white/15 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white">
                        INTERNAL ADMIN VIEW
                    </span>
                    <span>
                        SEO &amp; readability audit telemetry is visible only inside the Editorial Admin Panel.
                    </span>
                </p>
                <span class="text-purple-200/80">
                    Independent Fan CMS &bull; Not affiliated with Rockstar Games or Take-Two Interactive
                </span>
            </div>
        </div>

        <div class="bg-[#24103f] text-white">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-3 sm:px-6">
                <div class="flex items-center gap-3">
                    <a href="{{ route('studio.index') }}" class="flex items-center gap-2 no-underline">
                        <span class="rounded-sm border-b-2 border-[#be185d] bg-[#331858] px-2 py-1 text-xs font-bold uppercase tracking-wide text-white">
                            ADMIN
                        </span>
                        <span class="text-lg font-bold tracking-tight text-white">
                            GTAVI.PRO <span class="font-medium text-purple-200">Studio</span>
                        </span>
                    </a>
                    <span class="hidden border-l border-white/20 pl-3 text-xs font-medium text-purple-200/85 sm:inline">
                        Editorial &amp; SEO Command Center
                    </span>
                </div>

                <nav aria-label="Admin Navigation" class="flex flex-wrap items-center gap-2">
                    <a
                        href="{{ route('studio.index') }}"
                        @if ($routeName === 'studio.index' || $routeName === 'admin.index') aria-current="page" @endif
                        class="inline-flex min-h-[40px] items-center rounded border px-3.5 py-1.5 text-xs font-semibold transition {{ $routeName === 'studio.index' || $routeName === 'admin.index' ? 'border-white/30 bg-white/15 text-white' : 'border-white/15 bg-transparent text-purple-200 hover:bg-white/10 hover:text-white' }}"
                    >
                        Admin Dashboard
                    </a>
                    <a
                        href="{{ route('studio.create') }}"
                        @if ($routeName === 'studio.create') aria-current="page" @endif
                        class="inline-flex min-h-[40px] items-center rounded bg-[#be185d] px-4 py-1.5 text-xs font-semibold text-white transition hover:bg-[#9d174d]"
                    >
                        + New Article
                    </a>
                    <a
                        href="{{ route('home') }}"
                        class="inline-flex min-h-[40px] items-center rounded border border-white/20 bg-white/10 px-3.5 py-1.5 text-xs font-medium text-white transition hover:bg-white/20"
                    >
                        View Public Site &rarr;
                    </a>
                    @auth
                        <span class="hidden border-l border-white/20 pl-3 text-xs font-medium text-purple-200 md:inline">
                            {{ auth()->user()->name }}
                        </span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex min-h-[40px] cursor-pointer items-center rounded border border-white/20 bg-white/10 px-3.5 py-1.5 text-xs font-medium text-white transition hover:bg-white/20"
                            >
                                Sign Out
                            </button>
                        </form>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <main class="mx-auto min-h-[75dvh] w-full max-w-7xl px-4 py-6 sm:px-6 lg:py-8">
        @yield('content')
    </main>

    <footer class="mt-12 border-t border-slate-200 bg-white px-4 py-6 text-xs text-slate-500">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3">
            <p>
                <strong class="font-semibold text-slate-900">GTAVI.PRO Admin Panel</strong> &bull; Editorial CMS, SEO Audits &amp; Revision Control.
                Not affiliated with Rockstar Games or Take-Two Interactive.
            </p>
            <a href="{{ route('disclaimer') }}" class="font-semibold text-[#be185d] hover:text-[#2b124c]">
                Legal Disclaimer
            </a>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
