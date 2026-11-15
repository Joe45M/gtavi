@extends('layouts.app')

@section('content')
    <header class="rounded-md border border-slate-200 bg-white p-6 shadow-xs sm:p-8">
        <span class="inline-flex rounded border border-[#fbcfe8] bg-[#fdf2f8] px-2.5 py-0.5 text-xs font-semibold text-[#9d174d]">
            Geography Desk &bull; Cartography &amp; Biomes
        </span>
        <h1 id="main-heading" tabindex="-1" class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
            State of Leonida Map Atlas
        </h1>
        <p class="article-lead-summary mt-3 max-w-3xl text-base leading-relaxed text-slate-600">
            Explore the six major counties and ecological zones across the projected <strong class="font-semibold text-[#2b124c]">{{ number_format($totalArea, 1) }} km²</strong> State of Leonida footprint, comparing confirmed Trailer 1 regions with cartographic projections.
        </p>
    </header>

    <section aria-labelledby="regions-grid-heading" class="mt-10">
        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-4 border-b border-slate-200 pb-2.5">
            <h2 id="regions-grid-heading" class="text-xl font-bold text-slate-900 sm:text-2xl">Confirmed &amp; Projected Leonida Regions</h2>
            <a href="{{ route('calculators', ['tab' => 'map']) }}" class="text-sm font-semibold text-[#be185d] hover:text-[#2b124c]">
                Open Map Travel Time Calculator &rarr;
            </a>
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($locations as $location)
                <article class="flex flex-col rounded-md border border-slate-200 bg-white p-6 shadow-xs">
                    <div class="flex items-center justify-between gap-2">
                        <span class="rounded border border-slate-200 bg-[#f8f7fb] px-2.5 py-0.5 font-mono text-xs font-semibold text-[#2b124c]">
                            {{ $location->region_code }}
                        </span>
                        <span class="rounded px-2.5 py-0.5 text-xs font-semibold {{ $location->verification_status === 'trailer_confirmed' ? 'border border-emerald-200 bg-emerald-50 text-emerald-800' : 'border border-purple-200 bg-purple-50 text-[#2b124c]' }}">
                            {{ str_replace('_', ' ', ucfirst($location->verification_status)) }}
                        </span>
                    </div>

                    <h3 class="mt-3 text-xl font-bold text-slate-900">{{ $location->name }}</h3>
                    <p class="mt-1 text-xs font-semibold text-[#be185d]">
                        Inspiration: {{ $location->real_world_inspiration }} &bull; ~{{ number_format($location->estimated_area_sq_km, 1) }} km²
                    </p>
                    <p class="mt-3 flex-1 text-sm leading-relaxed text-slate-600">
                        {{ $location->description }}
                    </p>

                    <div class="mt-4 border-t border-slate-100 pt-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Key Landmarks</p>
                        <ul class="mt-2 flex flex-wrap gap-1.5">
                            @foreach (($location->key_landmarks ?? []) as $landmark)
                                <li class="rounded border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs text-slate-700">
                                    {{ $landmark }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section aria-labelledby="map-table-heading" class="mt-12">
        <h2 id="map-table-heading" class="mb-4 border-b border-slate-200 pb-2.5 text-xl font-bold text-slate-900 sm:text-2xl">Leonida District Scale &amp; Vehicle Matrix</h2>
        <div tabindex="0" role="region" aria-labelledby="map-matrix-caption" class="table-wrapper">
            <table class="vice-table" style='--label-1: "Region"; --label-2: "Code"; --label-3: "Biome"; --label-4: "Est. Area"; --label-5: "Vehicles";'>
                <caption id="map-matrix-caption">Projected Area and Dominant Transport Modes Across Leonida</caption>
                <thead>
                    <tr>
                        <th scope="col">Region</th>
                        <th scope="col">Code</th>
                        <th scope="col">Biome Type</th>
                        <th scope="col">Est. Area</th>
                        <th scope="col">Primary Vehicles</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($locations as $location)
                        <tr>
                            <th scope="row">{{ $location->name }}</th>
                            <td class="font-mono font-semibold text-[#2b124c]">{{ $location->region_code }}</td>
                            <td>{{ $location->biome_type }}</td>
                            <td class="font-bold text-slate-900">{{ number_format($location->estimated_area_sq_km, 1) }} km²</td>
                            <td>{{ implode(', ', $location->vehicle_types ?? []) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
