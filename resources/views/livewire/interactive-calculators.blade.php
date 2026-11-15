<div class="rounded-md border border-slate-200 bg-white p-5 shadow-xs sm:p-7">
    <!-- Accessible Tablist (`tabs` guidance) -->
    <div focusgroup="tablist nomemory" aria-label="GTA VI Telemetry Calculators" class="vice-tablist">
        <button
            type="button"
            id="tab-download"
            wire:click="selectTab('download')"
            aria-selected="{{ $activeTab === 'download' ? 'true' : 'false' }}"
            @if ($activeTab === 'download') focusgroupstart @endif
            class="vice-tab-btn"
        >
            1. Download Time &amp; Bandwidth
        </button>

        <button
            type="button"
            id="tab-pc"
            wire:click="selectTab('pc')"
            aria-selected="{{ $activeTab === 'pc' ? 'true' : 'false' }}"
            @if ($activeTab === 'pc') focusgroupstart @endif
            class="vice-tab-btn"
        >
            2. PC Rig &amp; FPS Readiness
        </button>

        <button
            type="button"
            id="tab-edition"
            wire:click="selectTab('edition')"
            aria-selected="{{ $activeTab === 'edition' ? 'true' : 'false' }}"
            @if ($activeTab === 'edition') focusgroupstart @endif
            class="vice-tab-btn"
        >
            3. Edition &amp; Currency Value
        </button>

        <button
            type="button"
            id="tab-map"
            wire:click="selectTab('map')"
            aria-selected="{{ $activeTab === 'map' ? 'true' : 'false' }}"
            @if ($activeTab === 'map') focusgroupstart @endif
            class="vice-tab-btn"
        >
            4. Leonida Map Scale &amp; Travel
        </button>
    </div>

    <div class="tabpanel-stack">
        <!-- Panel 1: Download Time Calculator -->
        <div
            role="tabpanel"
            aria-labelledby="tab-download"
            @if ($activeTab !== 'download') hidden="until-found" @endif
        >
            <div class="tabpanel-inner">
                <div class="grid gap-8 lg:grid-cols-12">
                    <div class="space-y-5 lg:col-span-6">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 sm:text-xl">Pre-Load &amp; Bandwidth Time Estimator</h3>
                            <p class="mt-1 text-sm text-slate-600">
                                Choose a quick platform preset or dial in your exact ISP download speed and PSN/Xbox CDN overhead.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="applyDownloadPreset(150, 'Mbps', 155)" class="min-h-[38px] cursor-pointer rounded border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-[#2b124c] hover:text-[#2b124c]">
                                PS5 Base (155 GB @ 150 Mbps)
                            </button>
                            <button type="button" wire:click="applyDownloadPreset(500, 'Mbps', 180)" class="min-h-[38px] cursor-pointer rounded border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-[#2b124c] hover:text-[#2b124c]">
                                PS5 Pro + Pack (180 GB @ 500 Mbps)
                            </button>
                            <button type="button" wire:click="applyDownloadPreset(1, 'Gbps', 225)" class="min-h-[38px] cursor-pointer rounded border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-[#2b124c] hover:text-[#2b124c]">
                                PC Ultra 4K (225 GB @ 1 Gbps)
                            </button>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="calc-speed" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">
                                    Connection Speed
                                </label>
                                <div class="mt-1.5 flex gap-2">
                                    <input
                                        id="calc-speed"
                                        type="number"
                                        min="1"
                                        max="100000"
                                        step="1"
                                        wire:model.live.debounce.200ms="speedValue"
                                        class="w-full rounded border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-900 focus:border-[#2b124c] focus:outline-none"
                                    >
                                    <select
                                        aria-label="Speed unit"
                                        wire:model.live="speedUnit"
                                        class="rounded border border-slate-300 bg-white px-3 py-2.5 text-sm font-semibold text-[#2b124c] focus:border-[#2b124c] focus:outline-none"
                                    >
                                        <option value="Mbps">Mbps</option>
                                        <option value="MBps">MB/s</option>
                                        <option value="Gbps">Gbps</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="calc-size" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">
                                    Projected Package Size (GB)
                                </label>
                                <input
                                    id="calc-size"
                                    type="number"
                                    min="10"
                                    max="500"
                                    wire:model.live.debounce.200ms="sizeGb"
                                    class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-900 focus:border-[#2b124c] focus:outline-none"
                                >
                            </div>

                            <div>
                                <label for="calc-overhead" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                    CDN / Protocol Overhead (%)
                                </label>
                                <input
                                    id="calc-overhead"
                                    type="number"
                                    min="0"
                                    max="40"
                                    wire:model.live.debounce.200ms="overheadPercent"
                                    class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-900 focus:border-[#2b124c] focus:outline-none"
                                >
                            </div>

                            <div>
                                <label for="calc-cap" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                    Monthly ISP Data Cap (GB, opt.)
                                </label>
                                <input
                                    id="calc-cap"
                                    type="number"
                                    min="0"
                                    max="50000"
                                    wire:model.live.debounce.200ms="dataCapGb"
                                    class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-900 focus:border-[#2b124c] focus:outline-none"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="rounded-md border border-slate-200 border-l-3 border-l-[#2b124c] bg-[#f8f7fb] p-6 lg:col-span-6">
                        <p class="text-xs font-semibold uppercase tracking-wide text-[#be185d]">
                            Estimated Transfer Time
                        </p>
                        <p class="mt-2 text-3xl font-bold text-slate-900 sm:text-4xl">
                            {{ $this->downloadResult['formatted_duration'] }}
                        </p>
                        <p class="mt-1 text-sm text-slate-600">
                            Effective Throughput: <strong class="font-semibold text-slate-900">{{ $this->downloadResult['effective_mb_per_sec'] }} MB/s</strong>
                            ({{ $this->downloadResult['effective_mbps'] }} Mbps after {{ $overheadPercent }}% overhead)
                        </p>

                        <dl class="mt-5 grid grid-cols-3 gap-3 text-xs">
                            <div class="rounded border border-slate-200 bg-white p-3">
                                <dt class="font-medium text-slate-500">Pre-Load Buffer</dt>
                                <dd class="mt-1 text-base font-bold text-slate-900">~{{ $this->downloadResult['recommended_preload_hours'] }} hrs</dd>
                            </div>
                            <div class="rounded border border-slate-200 bg-white p-3">
                                <dt class="font-medium text-slate-500">Safe Free SSD</dt>
                                <dd class="mt-1 text-base font-bold text-slate-900">{{ $this->downloadResult['recommended_free_ssd_gb'] }} GB</dd>
                            </div>
                            <div class="rounded border border-slate-200 bg-white p-3">
                                <dt class="font-medium text-slate-500">Data Cap Usage</dt>
                                <dd class="mt-1 text-base font-bold text-[#2b124c]">
                                    {{ $this->downloadResult['data_cap_percent'] !== null ? $this->downloadResult['data_cap_percent'].'%' : 'Unlimited' }}
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-5 border-t border-slate-200 pt-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">Connection Benchmark Comparison</p>
                            <ul class="mt-2 space-y-2 text-xs">
                                @foreach ($this->downloadResult['tiers'] as $tier)
                                    <li class="flex items-center justify-between rounded border border-slate-200 bg-white px-3 py-2">
                                        <span class="text-slate-700">{{ $tier['label'] }}</span>
                                        <span class="font-mono font-semibold text-[#2b124c]">{{ $tier['duration'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel 2: PC Rig & FPS Readiness -->
        <div
            role="tabpanel"
            aria-labelledby="tab-pc"
            @if ($activeTab !== 'pc') hidden="until-found" @endif
        >
            <div class="tabpanel-inner">
                <div class="grid gap-8 lg:grid-cols-12">
                    <div class="space-y-4 lg:col-span-6">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 sm:text-xl">Projected RAGE 9 PC Readiness Estimator</h3>
                            <p class="mt-1 text-sm text-slate-600">
                                Model how your CPU, GPU, VRAM, and upscaling stack handle projected open-world ray-tracing budgets.
                            </p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="pc-cpu" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">CPU Tier</label>
                                <select id="pc-cpu" wire:model.live="cpuTier" class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3 py-2.5 text-sm font-medium text-slate-900 focus:border-[#2b124c] focus:outline-none">
                                    <option value="entry">Entry 6-Core (Ryzen 5 5600 / i5-12400)</option>
                                    <option value="mid">Mid 8-Core (Ryzen 7 5700X / i5-13600K)</option>
                                    <option value="high">High Cache (Ryzen 7 5800X3D / i7-13700K)</option>
                                    <option value="enthusiast">Enthusiast (Ryzen 7 7800X3D / 9800X3D)</option>
                                </select>
                            </div>

                            <div>
                                <label for="pc-gpu" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">GPU Tier</label>
                                <select id="pc-gpu" wire:model.live="gpuTier" class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3 py-2.5 text-sm font-medium text-slate-900 focus:border-[#2b124c] focus:outline-none">
                                    <option value="entry">Entry (RTX 3060 / RX 6650 XT)</option>
                                    <option value="mid">Midrange (RTX 4060 Ti / RX 7700 XT)</option>
                                    <option value="high">Upper (RTX 4070 Super / RX 7900 XT)</option>
                                    <option value="enthusiast">Flagship (RTX 4080S / 4090 / 5080)</option>
                                </select>
                            </div>

                            <div>
                                <label for="pc-vram" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">GPU VRAM (GB)</label>
                                <select id="pc-vram" wire:model.live="vramGb" class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3 py-2.5 text-sm font-medium text-slate-900 focus:border-[#2b124c] focus:outline-none">
                                    <option value="8">8 GB VRAM</option>
                                    <option value="10">10 GB VRAM</option>
                                    <option value="12">12 GB VRAM</option>
                                    <option value="16">16 GB VRAM</option>
                                    <option value="24">24 GB VRAM</option>
                                </select>
                            </div>

                            <div>
                                <label for="pc-ram" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">System RAM (GB)</label>
                                <select id="pc-ram" wire:model.live="ramGb" class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3 py-2.5 text-sm font-medium text-slate-900 focus:border-[#2b124c] focus:outline-none">
                                    <option value="8">8 GB</option>
                                    <option value="16">16 GB</option>
                                    <option value="32">32 GB</option>
                                    <option value="64">64 GB</option>
                                </select>
                            </div>

                            <div>
                                <label for="pc-res" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Target Output</label>
                                <select id="pc-res" wire:model.live="resolution" class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3 py-2.5 text-sm font-medium text-slate-900 focus:border-[#2b124c] focus:outline-none">
                                    <option value="1080p">1080p (Full HD)</option>
                                    <option value="1440p">1440p (QHD)</option>
                                    <option value="4k">2160p (4K UHD)</option>
                                </select>
                            </div>

                            <div>
                                <label for="pc-upscaler" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Upscaling Mode</label>
                                <select id="pc-upscaler" wire:model.live="upscaler" class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3 py-2.5 text-sm font-medium text-slate-900 focus:border-[#2b124c] focus:outline-none">
                                    <option value="native">Native TAA (No Upscaler)</option>
                                    <option value="dlss_quality">DLSS Quality</option>
                                    <option value="fsr_quality">FSR 3 Quality</option>
                                    <option value="dlss_fg">DLSS Quality + Frame Gen</option>
                                </select>
                            </div>
                        </div>

                        <label class="flex cursor-pointer items-center gap-3 rounded border border-slate-200 bg-slate-50 p-3.5 text-sm font-medium text-slate-800">
                            <input type="checkbox" wire:model.live="rayTracing" class="h-4 w-4 accent-[#2b124c]">
                            <span>Enable Projected Hardware Ray-Traced Global Illumination (RTGI)</span>
                        </label>
                    </div>

                    <div class="rounded-md border border-slate-200 border-l-3 border-l-[#2b124c] bg-[#f8f7fb] p-6 lg:col-span-6">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-[#be185d]">Projected Rig Readiness</p>
                                <p class="mt-1 text-3xl font-bold text-slate-900 sm:text-4xl">{{ $this->pcResult['readiness_score'] }}<span class="text-lg font-medium text-slate-500">/100</span></p>
                            </div>
                            <span class="rounded bg-[#2b124c] px-3 py-1 text-xs font-semibold text-white">
                                {{ $this->pcResult['tier_label'] }}
                            </span>
                        </div>

                        <div class="mt-5 grid grid-cols-3 gap-3 text-center text-xs">
                            <div class="rounded border border-slate-200 bg-white p-3">
                                <span class="block text-slate-500">1% Low FPS</span>
                                <strong class="mt-1 block text-xl font-bold text-amber-700">{{ $this->pcResult['estimated_fps']['min'] }}</strong>
                            </div>
                            <div class="rounded border border-[#2b124c] bg-white p-3">
                                <span class="block font-semibold text-[#be185d]">Projected Avg</span>
                                <strong class="mt-1 block text-2xl font-bold text-slate-900">{{ $this->pcResult['estimated_fps']['avg'] }} FPS</strong>
                            </div>
                            <div class="rounded border border-slate-200 bg-white p-3">
                                <span class="block text-slate-500">Peak FPS</span>
                                <strong class="mt-1 block text-xl font-bold text-emerald-700">{{ $this->pcResult['estimated_fps']['max'] }}</strong>
                            </div>
                        </div>

                        <dl class="mt-5 space-y-2.5 border-t border-slate-200 pt-4 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-600">Primary Bottleneck</dt>
                                <dd class="font-semibold text-[#be185d]">{{ $this->pcResult['bottleneck'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-600">Recommended Preset</dt>
                                <dd class="font-semibold text-slate-900">{{ $this->pcResult['recommended_preset'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-600">Projected VRAM Target</dt>
                                <dd class="font-semibold text-slate-900">
                                    {{ $this->pcResult['vram_required_gb'] }} GB
                                    ({{ $this->pcResult['vram_headroom_gb'] >= 0 ? '+'.$this->pcResult['vram_headroom_gb'].' GB headroom' : $this->pcResult['vram_headroom_gb'].' GB deficit' }})
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel 3: Edition & Currency Value -->
        <div
            role="tabpanel"
            aria-labelledby="tab-edition"
            @if ($activeTab !== 'edition') hidden="until-found" @endif
        >
            <div class="tabpanel-inner">
                <div class="grid gap-8 lg:grid-cols-12">
                    <div class="space-y-4 lg:col-span-6">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 sm:text-xl">Edition Pricing &amp; Cost-Per-Hour Analyzer</h3>
                            <p class="mt-1 text-sm text-slate-600">
                                Evaluate projected storefront pricing across currencies against your expected campaign and online playtime.
                            </p>
                        </div>

                        <div>
                            <label for="ed-select" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">Projected Edition</label>
                            <select id="ed-select" wire:model.live="editionId" class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-900 focus:border-[#2b124c] focus:outline-none">
                                @foreach ($this->editions as $edition)
                                    <option value="{{ $edition->id }}">{{ $edition->name }} (${{ number_format($edition->price_usd, 2) }} est.)</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="ed-currency" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Storefront Currency</label>
                                <select id="ed-currency" wire:model.live="currency" class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-900 focus:border-[#2b124c] focus:outline-none">
                                    <option value="USD">USD ($)</option>
                                    <option value="GBP">GBP (£)</option>
                                    <option value="EUR">EUR (€)</option>
                                    <option value="CAD">CAD (CA$)</option>
                                    <option value="AUD">AUD (A$)</option>
                                    <option value="JPY">JPY (¥)</option>
                                </select>
                            </div>

                            <div>
                                <label for="ed-hours" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Expected Play Hours</label>
                                <input
                                    id="ed-hours"
                                    type="number"
                                    min="10"
                                    max="2000"
                                    wire:model.live.debounce.200ms="expectedPlayHours"
                                    class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-900 focus:border-[#2b124c] focus:outline-none"
                                >
                            </div>
                        </div>
                    </div>

                    @if (!empty($this->editionResult))
                        <div class="rounded-md border border-slate-200 border-l-3 border-l-[#2b124c] bg-[#f8f7fb] p-6 lg:col-span-6">
                            <span class="inline-flex rounded border border-[#fbcfe8] bg-[#fdf2f8] px-2.5 py-0.5 text-xs font-semibold text-[#9d174d]">
                                {{ $this->editionResult['value_verdict'] }}
                            </span>
                            <p class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">
                                {{ $this->editionResult['formatted_price'] }}
                                <span class="text-sm font-medium text-slate-500">{{ $this->editionResult['currency'] }} (Projected)</span>
                            </p>
                            <p class="mt-1 text-sm text-slate-600">
                                Cost Per Hour over {{ $expectedPlayHours }}h: <strong class="font-semibold text-[#2b124c]">{{ $this->editionResult['formatted_cost_per_hour'] }}/hr</strong>
                            </p>

                            <dl class="mt-5 grid grid-cols-3 gap-3 text-xs">
                                <div class="rounded border border-slate-200 bg-white p-3">
                                    <dt class="font-medium text-slate-500">Early Access Est.</dt>
                                    <dd class="mt-1 text-base font-bold text-slate-900">{{ $this->editionResult['early_access_hours'] }} hrs</dd>
                                </div>
                                <div class="rounded border border-slate-200 bg-white p-3">
                                    <dt class="font-medium text-slate-500">Online Credits</dt>
                                    <dd class="mt-1 text-base font-bold text-slate-900">${{ number_format($this->editionResult['online_bonus_credits']) }}</dd>
                                </div>
                                <div class="rounded border border-slate-200 bg-white p-3">
                                    <dt class="font-medium text-slate-500">Est. Package</dt>
                                    <dd class="mt-1 text-base font-bold text-[#2b124c]">{{ $this->editionResult['estimated_size_gb'] }} GB</dd>
                                </div>
                            </dl>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Panel 4: Leonida Map Scale & Travel -->
        <div
            role="tabpanel"
            aria-labelledby="tab-map"
            @if ($activeTab !== 'map') hidden="until-found" @endif
        >
            <div class="tabpanel-inner">
                <div class="grid gap-8 lg:grid-cols-12">
                    <div class="space-y-4 lg:col-span-6">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 sm:text-xl">State of Leonida Scale &amp; Traversal Estimator</h3>
                            <p class="mt-1 text-sm text-slate-600">
                                Compare projected Leonida (~125 km²) against Los Santos (75.8 km²) and calculate cross-state driving or flight times.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="applyVehiclePreset(95)" class="min-h-[38px] cursor-pointer rounded border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-[#2b124c] hover:text-[#2b124c]">
                                Airboat (95 km/h)
                            </button>
                            <button type="button" wire:click="applyVehiclePreset(260)" class="min-h-[38px] cursor-pointer rounded border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-[#2b124c] hover:text-[#2b124c]">
                                Supercar (260 km/h)
                            </button>
                            <button type="button" wire:click="applyVehiclePreset(420)" class="min-h-[38px] cursor-pointer rounded border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-[#2b124c] hover:text-[#2b124c]">
                                Jet Aircraft (420 km/h)
                            </button>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="map-speed" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">Vehicle Speed (km/h)</label>
                                <input
                                    id="map-speed"
                                    type="number"
                                    min="10"
                                    max="450"
                                    wire:model.live.debounce.200ms="vehicleSpeedKmh"
                                    class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-900 focus:border-[#2b124c] focus:outline-none"
                                >
                            </div>

                            <div>
                                <label for="map-region" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Target Leonida County</label>
                                <select id="map-region" wire:model.live="locationId" class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-900 focus:border-[#2b124c] focus:outline-none">
                                    @foreach ($this->locations as $loc)
                                        <option value="{{ $loc->id }}">{{ $loc->name }} ({{ number_format($loc->estimated_area_sq_km, 1) }} km²)</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-md border border-slate-200 border-l-3 border-l-[#2b124c] bg-[#f8f7fb] p-6 lg:col-span-6">
                        <p class="text-xs font-semibold uppercase tracking-wide text-[#be185d]">Projected Diagonal Crossing</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900 sm:text-4xl">
                            {{ $this->mapResult['leonida_crossing_minutes'] }} min
                        </p>
                        <p class="mt-1 text-sm text-slate-600">
                            Leonida is projected at <strong class="font-semibold text-slate-900">{{ $this->mapResult['multiplier_vs_gta5'] }}x</strong> GTA V Los Santos and <strong class="font-semibold text-slate-900">{{ $this->mapResult['multiplier_vs_vice_1986'] }}x</strong> 1986 Vice City.
                        </p>

                        <dl class="mt-5 grid grid-cols-2 gap-3 text-xs">
                            <div class="rounded border border-slate-200 bg-white p-3">
                                <dt class="font-medium text-slate-500">Selected Zone Crossing</dt>
                                <dd class="mt-1 text-base font-bold text-slate-900">
                                    {{ $this->mapResult['selected_region_minutes'] ?? '—' }} min
                                </dd>
                                <dd class="text-[11px] text-slate-500">{{ $this->mapResult['selected_region_name'] }}</dd>
                            </div>
                            <div class="rounded border border-slate-200 bg-white p-3">
                                <dt class="font-medium text-slate-500">Leonida vs. GTA V</dt>
                                <dd class="mt-1 text-base font-bold text-[#2b124c]">
                                    ~{{ $this->mapResult['leonida_area_sq_km'] }} km² vs {{ $this->mapResult['los_santos_area_sq_km'] }} km²
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
