@extends('layouts.app')

@section('content')
    <header class="rounded-md border border-slate-200 bg-white p-6 shadow-xs sm:p-8">
        <span class="inline-flex rounded border border-[#fbcfe8] bg-[#fdf2f8] px-2.5 py-0.5 text-xs font-semibold text-[#9d174d]">
            Tools Desk &bull; Interactive Calculators
        </span>
        <h1 id="main-heading" tabindex="-1" class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
            GTA VI Calculators: Download, PC Specs, Editions &amp; Map Scale
        </h1>
        <p class="mt-3 max-w-3xl text-base leading-relaxed text-slate-600">
            Independent estimation models built for pre-launch bandwidth planning, rig upgrades, regional edition budgeting, and State of Leonida traversal timing.
        </p>
    </header>

    <section aria-label="Interactive GTA VI Calculator Workbench" class="mt-8">
        <livewire:interactive-calculators :initial-tab="$initialTab" />
    </section>

    <section aria-labelledby="calc-methodology-heading" class="mt-10 rounded-md border border-slate-200 border-l-3 border-l-[#2b124c] bg-white p-6 shadow-xs sm:p-8">
        <h2 id="calc-methodology-heading" class="text-xl font-bold text-slate-900">Calculation Methodology &amp; Independence Notice</h2>
        <div class="mt-3 grid gap-6 text-sm leading-relaxed text-slate-600 md:grid-cols-2">
            <p>
                <strong class="font-semibold text-slate-900">Download Overhead Math:</strong> Real-world PlayStation Network, Xbox Live, and PC launcher downloads lose roughly 6%–12% throughput to TCP framing, TLS negotiation, and CDN edge congestion. Our calculator converts megabits/s to megabytes/s (<code class="rounded border border-slate-200 bg-slate-100 px-1.5 py-0.5 text-[#2b124c]">Mbps / 8</code>) after overhead and adds a 25% temporary SSD decompression buffer.
            </p>
            <p>
                <strong class="font-semibold text-slate-900">Projection Disclaimer:</strong> Official system requirements, package sizes, and retail pricing are controlled solely by Rockstar Games and Take-Two Interactive. GTAVI.PRO is an independent fan resource with zero corporate affiliation.
            </p>
        </div>
    </section>
@endsection
