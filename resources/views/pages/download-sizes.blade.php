@extends('layouts.app')

@section('content')
    <header class="rounded-md border border-slate-200 bg-white p-6 shadow-xs sm:p-8">
        <span class="inline-flex rounded border border-[#fbcfe8] bg-[#fdf2f8] px-2.5 py-0.5 text-xs font-semibold text-[#9d174d]">
            Hardware Desk &bull; Storage &amp; NVMe Benchmarks
        </span>
        <h1 id="main-heading" tabindex="-1" class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
            GTA VI Download Sizes &amp; Hardware Specs
        </h1>
        <p class="article-lead-summary mt-3 max-w-3xl text-base leading-relaxed text-slate-600">
            Projected install footprints, optional high-resolution texture packs, SSD throughput minimums, and ray-tracing tiers across PS5, PS5 Pro, Xbox Series X|S, and PC.
            Official sizes await Rockstar Games confirmation.
        </p>
    </header>

    <section aria-labelledby="specs-table-heading" class="mt-10">
        <div class="mb-4 flex flex-wrap items-baseline justify-between gap-4 border-b border-slate-200 pb-2.5">
            <h2 id="specs-table-heading" class="text-xl font-bold text-slate-900 sm:text-2xl">Complete Platform Storage &amp; Target Spec Matrix</h2>
            <a href="{{ route('calculators', ['tab' => 'download']) }}" class="text-sm font-semibold text-[#be185d] hover:text-[#2b124c]">
                Calculate Exact Download Time &rarr;
            </a>
        </div>

        <div tabindex="0" role="region" aria-labelledby="download-specs-caption" class="table-wrapper">
            <table class="vice-table" style='--label-1: "Platform"; --label-2: "Base Size"; --label-3: "Total w/ Pack"; --label-4: "Resolution & FPS"; --label-5: "Upscaler"; --label-6: "Min SSD"; --label-7: "RAM / VRAM";'>
                <caption id="download-specs-caption">Projected Download Footprint &amp; Streaming Requirements by Platform</caption>
                <thead>
                    <tr>
                        <th scope="col">Platform</th>
                        <th scope="col">Base Size</th>
                        <th scope="col">Total w/ Hi-Res</th>
                        <th scope="col">Resolution &amp; FPS</th>
                        <th scope="col">Upscaler</th>
                        <th scope="col">Min SSD Speed</th>
                        <th scope="col">RAM / VRAM</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($platforms as $platform)
                        <tr>
                            <th scope="row">{{ $platform->platform_name }}</th>
                            <td class="font-bold text-slate-900">{{ $platform->estimated_download_gb }} GB</td>
                            <td class="font-bold text-[#2b124c]">{{ $platform->totalSizeWithHighResGb() }} GB</td>
                            <td>{{ $platform->target_resolution }} &bull; {{ $platform->target_fps }}</td>
                            <td>{{ $platform->upscaling_tech }}</td>
                            <td>{{ number_format($platform->min_ssd_speed_mbps) }} MB/s</td>
                            <td>{{ $platform->ram_gb }} GB RAM / {{ $platform->vram_gb }} GB VRAM</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section aria-labelledby="platform-notes-heading" class="mt-10 grid gap-5 md:grid-cols-2">
        <h2 id="platform-notes-heading" class="sr-only">Detailed Platform Hardware Notes</h2>
        @foreach ($platforms as $platform)
            <article class="rounded-md border border-slate-200 border-l-3 border-l-[#2b124c] bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-lg font-bold text-slate-900">{{ $platform->platform_name }}</h3>
                    <span class="rounded border border-purple-200 bg-purple-50 px-2.5 py-0.5 text-xs font-semibold uppercase text-[#2b124c]">
                        {{ $platform->platform_family }}
                    </span>
                </div>
                <p class="mt-2 font-mono text-xs text-slate-600">
                    CPU: {{ $platform->cpu_requirement }} | GPU: {{ $platform->gpu_requirement }}
                </p>
                <p class="mt-1.5 text-xs font-medium text-slate-500">
                    Ray Tracing: {{ $platform->ray_tracing_tier }}
                </p>
                <p class="mt-3 text-sm leading-relaxed text-slate-700">{{ $platform->notes }}</p>
            </article>
        @endforeach
    </section>
@endsection
