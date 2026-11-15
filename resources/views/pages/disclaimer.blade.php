@extends('layouts.app')

@section('content')
    <article class="mx-auto max-w-4xl rounded-md border border-slate-200 bg-white p-6 shadow-xs sm:p-10">
        <span class="inline-flex rounded border border-[#fbcfe8] bg-[#fdf2f8] px-2.5 py-0.5 text-xs font-semibold text-[#9d174d]">
            Editorial Standards &amp; Legal Notice
        </span>
        <h1 id="main-heading" tabindex="-1" class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
            Legal Disclaimer &amp; Non-Affiliation Policy
        </h1>

        <div class="mt-6 space-y-6 text-base leading-relaxed text-slate-700">
            <section class="rounded-md border border-[#fbcfe8] border-l-3 border-l-[#be185d] bg-[#fdf2f8] p-5">
                <h2 class="text-lg font-bold text-slate-900">1. Complete Non-Affiliation Statement</h2>
                <p class="mt-2 text-slate-800">
                    <strong class="font-semibold text-slate-900">GTAVI.PRO</strong> is an independently owned fan community, telemetry reference, and technical news website.
                    <strong class="font-semibold text-slate-900">GTAVI.PRO is NOT affiliated with, endorsed by, sponsored by, authorized by, or connected in any way to Rockstar Games, Rockstar North, Take-Two Interactive Software, Inc., or any parent, subsidiary, or partner entities.</strong>
                </p>
            </section>

            <section>
                <h2 class="text-lg font-bold text-slate-900">2. Trademarks &amp; Intellectual Property</h2>
                <p class="mt-2">
                    <em>Grand Theft Auto</em>, <em>Grand Theft Auto VI</em>, <em>GTA VI</em>, <em>Vice City</em>, <em>Leonida</em>, and <em>RAGE (Rockstar Advanced Game Engine)</em> are trademarks or registered trademarks of Take-Two Interactive Software, Inc. Any reference to these names on GTAVI.PRO is strictly for descriptive, journalistic, educational, and analytical purposes under nominative fair use.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-bold text-slate-900">3. Methodology: Confirmed Filings vs. Technical Projections</h2>
                <p class="mt-2">
                    Every article, table, and calculator on GTAVI.PRO explicitly tags information into distinct verification tiers:
                </p>
                <ul class="mt-3 list-disc space-y-2 ps-6 text-slate-600">
                    <li><strong class="font-semibold text-slate-900">Verified Filing / Confirmed Fact:</strong> Statements published directly by Rockstar Games Newswire or Take-Two Interactive Investor Relations.</li>
                    <li><strong class="font-semibold text-slate-900">Technical Projection:</strong> Independent hardware estimates (download sizes, NVMe throughput, PC VRAM tiers, map area estimates, and projected regional edition MSRPs) modeled from publicly released engine footage and historical AAA industry benchmarks.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-lg font-bold text-slate-900">4. No Pre-Order Sales or Account Collection</h2>
                <p class="mt-2">
                    GTAVI.PRO does not sell game keys, collect payment information, or distribute beta access. Always purchase official editions exclusively through Rockstar Games, PlayStation Store, Xbox Store, or authorized retailers.
                </p>
            </section>
        </div>
    </article>
@endsection
