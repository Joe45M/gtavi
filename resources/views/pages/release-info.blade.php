@extends('layouts.app')

@section('content')
    <header class="rounded-md border border-slate-200 bg-white p-6 shadow-xs sm:p-8">
        <span class="inline-flex rounded border border-[#fbcfe8] bg-[#fdf2f8] px-2.5 py-0.5 text-xs font-semibold text-[#9d174d]">
            Official Filings &amp; Milestone Ledger
        </span>
        <h1 id="main-heading" tabindex="-1" class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
            GTA VI Release Info &amp; Verified Timeline
        </h1>
        <p class="article-lead-summary mt-3 max-w-3xl text-base leading-relaxed text-slate-600">
            GTAVI.PRO separates officially published Rockstar Games Newswire and Take-Two Interactive investor disclosures from industry release-cadence projections.
            <strong class="font-semibold text-slate-900">Not affiliated with Rockstar Games or Take-Two Interactive.</strong>
        </p>
    </header>

    <!-- Chronological Timeline -->
    <section aria-labelledby="timeline-heading" class="mt-10">
        <h2 id="timeline-heading" class="border-b border-slate-200 pb-2.5 text-xl font-bold text-slate-900 sm:text-2xl">
            Chronological Announcement &amp; Guidance History
        </h2>
        <ol class="mt-6 space-y-4">
            @foreach ($milestones as $milestone)
                <li class="rounded-md border border-slate-200 border-l-3 {{ $milestone->is_confirmed ? 'border-l-emerald-600 bg-white' : 'border-l-[#2b124c] bg-white' }} p-5 shadow-xs sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="rounded border border-slate-200 bg-[#f8f7fb] px-2.5 py-1 font-mono text-xs font-semibold text-[#2b124c]">
                                @if ($milestone->milestone_date)
                                    <time datetime="{{ $milestone->milestone_date->toDateString() }}">{{ $milestone->display_date_label }}</time>
                                @else
                                    {{ $milestone->display_date_label }}
                                @endif
                            </span>
                            <span class="rounded px-2.5 py-0.5 text-xs font-semibold {{ $milestone->is_confirmed ? 'border border-emerald-200 bg-emerald-50 text-emerald-800' : 'border border-purple-200 bg-purple-50 text-[#2b124c]' }}">
                                {{ $milestone->is_confirmed ? 'Verified Filing / Announcement' : 'Projected Tracker' }}
                            </span>
                        </div>
                        <span class="text-xs font-medium text-slate-500">
                            Source: {{ $milestone->source_name }}
                        </span>
                    </div>

                    <h3 class="mt-3 text-lg font-bold text-slate-900">{{ $milestone->title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $milestone->description }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    <!-- Platform Release Matrix -->
    <section aria-labelledby="platform-windows-heading" class="mt-12">
        <h2 id="platform-windows-heading" class="mb-4 border-b border-slate-200 pb-2.5 text-xl font-bold text-slate-900 sm:text-2xl">
            Platform Target Windows &amp; Hardware Availability
        </h2>

        <div tabindex="0" role="region" aria-labelledby="release-platforms-caption" class="table-wrapper">
            <table class="vice-table" style='--label-1: "Platform"; --label-2: "Family"; --label-3: "Release Window"; --label-4: "Target Performance"; --label-5: "Status";'>
                <caption id="release-platforms-caption">Console First vs. Projected PC Port Windows</caption>
                <thead>
                    <tr>
                        <th scope="col">Platform</th>
                        <th scope="col">Family</th>
                        <th scope="col">Release Window</th>
                        <th scope="col">Target Performance</th>
                        <th scope="col">Verification</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($platforms as $platform)
                        <tr>
                            <th scope="row">{{ $platform->platform_name }}</th>
                            <td class="font-semibold uppercase text-[#2b124c]">{{ $platform->platform_family }}</td>
                            <td>{{ $platform->release_window }}</td>
                            <td>{{ $platform->target_resolution }} @ {{ $platform->target_fps }}</td>
                            <td>
                                <span class="inline-flex rounded px-2 py-0.5 text-xs font-semibold {{ $platform->release_status === 'confirmed_window' ? 'border border-emerald-200 bg-emerald-50 text-emerald-800' : 'border border-purple-200 bg-purple-50 text-[#2b124c]' }}">
                                    {{ $platform->release_status === 'confirmed_window' ? 'Confirmed Target Platform' : 'Projected Stagger' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
