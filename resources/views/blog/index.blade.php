@extends('layouts.app')

@section('content')
    <header class="rounded-md border border-slate-200 bg-white p-6 shadow-xs sm:p-8">
        <span class="inline-flex rounded border border-[#fbcfe8] bg-[#fdf2f8] px-2.5 py-0.5 text-xs font-semibold text-[#9d174d]">
            GTAVI.PRO Editorial Desk
        </span>
        <h1 id="main-heading" tabindex="-1" class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
            GTA VI News, Reports &amp; Deep Dives
        </h1>
        <p class="article-lead-summary mt-3 max-w-2xl text-base leading-relaxed text-slate-600">
            In-depth reporting covering GTA VI download sizes, Take-Two filings, RAGE 9 rendering architecture, regional pricing, and State of Leonida cartography.
        </p>
    </header>

    <div class="mt-8">
        <livewire:blog-index-filter />
    </div>
@endsection
