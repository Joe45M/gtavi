@extends('layouts.app')

@section('content')
    <header class="rounded-md border border-slate-200 bg-white p-6 shadow-xs sm:p-8">
        <span class="inline-flex rounded border border-[#fbcfe8] bg-[#fdf2f8] px-2.5 py-0.5 text-xs font-semibold text-[#9d174d]">
            Retail Desk &bull; Multi-Currency Comparison
        </span>
        <h1 id="main-heading" tabindex="-1" class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
            GTA VI Prices &amp; Editions Matrix
        </h1>
        <p class="article-lead-summary mt-3 max-w-3xl text-base leading-relaxed text-slate-600">
            Compare projected Standard, Deluxe, and Collector editions across USD, GBP, EUR, CAD, AUD, and JPY.
            Official retail SKUs have not yet been announced by Rockstar Games or Take-Two Interactive; all prices shown here are independent editorial projections.
        </p>
    </header>

    <!-- Edition Cards -->
    <section aria-labelledby="edition-cards-heading" class="mt-10">
        <h2 id="edition-cards-heading" class="border-b border-slate-200 pb-2.5 text-xl font-bold text-slate-900 sm:text-2xl">
            Projected Edition Breakdown
        </h2>
        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            @foreach ($editions as $edition)
                <article class="flex flex-col rounded-md border bg-white p-6 shadow-xs {{ $edition->is_highlighted ? 'border-[#2b124c] ring-1 ring-[#2b124c]/15' : 'border-slate-200' }}">
                    <div class="flex items-center justify-between gap-2">
                        <span class="rounded px-2.5 py-0.5 text-xs font-semibold {{ $edition->is_highlighted ? 'bg-[#2b124c] text-white' : 'border border-purple-200 bg-purple-50 text-[#2b124c]' }}">
                            {{ $edition->badge_text ?: 'Projected SKU' }}
                        </span>
                        <span class="font-mono text-xs font-semibold text-slate-500">~{{ $edition->estimated_size_gb }} GB</span>
                    </div>

                    <h3 class="mt-4 text-xl font-bold text-slate-900">{{ $edition->name }}</h3>
                    <p class="mt-1 text-3xl font-bold text-slate-900">
                        ${{ number_format($edition->price_usd, 2) }} <span class="text-sm font-medium text-slate-500">USD (Est.)</span>
                    </p>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $edition->tagline }}</p>

                    <dl class="mt-5 grid grid-cols-2 gap-3 rounded border border-slate-200 bg-[#f8f7fb] p-3 text-xs">
                        <div>
                            <dt class="font-medium text-slate-500">Early Access Est.</dt>
                            <dd class="mt-0.5 font-bold text-slate-900">{{ $edition->early_access_hours > 0 ? $edition->early_access_hours.' Hours' : 'Standard Day 1' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500">Online Credits Est.</dt>
                            <dd class="mt-0.5 font-bold text-[#2b124c]">${{ number_format($edition->online_bonus_credits) }}</dd>
                        </div>
                    </dl>

                    @if (!empty($edition->digital_bonuses))
                        <h4 class="mt-5 text-xs font-semibold uppercase tracking-wide text-[#9d174d]">Digital Inclusions (Projected)</h4>
                        <ul class="mt-2 space-y-1.5 text-sm text-slate-700">
                            @foreach ($edition->digital_bonuses as $bonus)
                                <li class="flex items-start gap-2">
                                    <span aria-hidden="true" class="text-[#be185d]">&bull;</span>
                                    <span>{{ $bonus }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if (!empty($edition->physical_collectibles))
                        <h4 class="mt-5 text-xs font-semibold uppercase tracking-wide text-[#2b124c]">Physical Collectibles (Projected)</h4>
                        <ul class="mt-2 space-y-1.5 text-sm text-slate-700">
                            @foreach ($edition->physical_collectibles as $item)
                                <li class="flex items-start gap-2">
                                    <span aria-hidden="true" class="text-[#2b124c]">&bull;</span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </article>
            @endforeach
        </div>
    </section>

    <!-- Full Multi-Currency Table -->
    <section aria-labelledby="currency-table-heading" class="mt-12">
        <div class="mb-4 flex flex-wrap items-baseline justify-between gap-4 border-b border-slate-200 pb-2.5">
            <h2 id="currency-table-heading" class="text-xl font-bold text-slate-900 sm:text-2xl">Regional Currency Parity Matrix</h2>
            <a href="{{ route('calculators', ['tab' => 'edition']) }}" class="text-sm font-semibold text-[#be185d] hover:text-[#2b124c]">
                Calculate Cost-Per-Hour &rarr;
            </a>
        </div>

        <div tabindex="0" role="region" aria-labelledby="currency-matrix-caption" class="table-wrapper">
            <table class="vice-table" style='--label-1: "Edition"; --label-2: "USD"; --label-3: "GBP"; --label-4: "EUR"; --label-5: "CAD"; --label-6: "AUD"; --label-7: "JPY";'>
                <caption id="currency-matrix-caption">Projected Regional Storefront MSRP Estimates (Tax-Adjusted Models)</caption>
                <thead>
                    <tr>
                        <th scope="col">Edition</th>
                        <th scope="col">USD ($)</th>
                        <th scope="col">GBP (£)</th>
                        <th scope="col">EUR (€)</th>
                        <th scope="col">CAD (CA$)</th>
                        <th scope="col">AUD (A$)</th>
                        <th scope="col">JPY (¥)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($editions as $edition)
                        <tr>
                            <th scope="row">{{ $edition->name }}</th>
                            <td class="font-bold text-slate-900">${{ number_format($edition->price_usd, 2) }}</td>
                            <td>£{{ number_format($edition->price_gbp, 2) }}</td>
                            <td>€{{ number_format($edition->price_eur, 2) }}</td>
                            <td>CA${{ number_format($edition->price_cad, 2) }}</td>
                            <td>A${{ number_format($edition->price_aud, 2) }}</td>
                            <td>¥{{ number_format($edition->price_jpy) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
