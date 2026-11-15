<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\GameEdition;
use App\Models\MapLocation;
use App\Services\CalculatorService;
use App\Services\MicrositeDataService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

final class InteractiveCalculators extends Component
{
    #[Url(as: 'tab')]
    public string $activeTab = 'download';

    // Download calculator state
    public float $speedValue = 300.0;

    public string $speedUnit = 'Mbps';

    public float $sizeGb = 165.0;

    public float $overheadPercent = 8.0;

    public ?int $dataCapGb = 1200;

    // PC readiness calculator state
    public string $cpuTier = 'high';

    public string $gpuTier = 'high';

    public int $ramGb = 32;

    public int $vramGb = 12;

    public string $resolution = '1440p';

    public bool $rayTracing = true;

    public string $upscaler = 'dlss_quality';

    // Edition value calculator state
    public int $editionId = 0;

    public string $currency = 'USD';

    public int $expectedPlayHours = 120;

    // Map scale calculator state
    public float $vehicleSpeedKmh = 260.0;

    public int $locationId = 0;

    public function mount(MicrositeDataService $data, ?string $initialTab = null): void
    {
        if ($initialTab !== null && in_array($initialTab, ['download', 'pc', 'edition', 'map'], true)) {
            $this->activeTab = $initialTab;
        } elseif (! in_array($this->activeTab, ['download', 'pc', 'edition', 'map'], true)) {
            $this->activeTab = 'download';
        }

        $firstEdition = $data->editions()->first();
        if ($firstEdition !== null && $this->editionId === 0) {
            $this->editionId = $firstEdition->id;
        }

        $firstLocation = $data->mapLocations()->first();
        if ($firstLocation !== null && $this->locationId === 0) {
            $this->locationId = $firstLocation->id;
        }
    }

    public function selectTab(string $tab): void
    {
        if (in_array($tab, ['download', 'pc', 'edition', 'map'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function applyDownloadPreset(float $speed, string $unit, float $size): void
    {
        $this->speedValue = $speed;
        $this->speedUnit = $unit;
        $this->sizeGb = $size;
    }

    public function applyVehiclePreset(float $speedKmh): void
    {
        $this->vehicleSpeedKmh = $speedKmh;
    }

    /**
     * @return Collection<int, GameEdition>
     */
    #[Computed]
    public function editions(): Collection
    {
        return app(MicrositeDataService::class)->editions();
    }

    /**
     * @return Collection<int, MapLocation>
     */
    #[Computed]
    public function locations(): Collection
    {
        return app(MicrositeDataService::class)->mapLocations();
    }

    /**
     * @return array<string, mixed>
     */
    #[Computed]
    public function downloadResult(): array
    {
        return app(CalculatorService::class)->calculateDownloadTime(
            speedValue: (float) $this->speedValue,
            unit: $this->speedUnit,
            sizeGb: (float) $this->sizeGb,
            overheadPercent: (float) $this->overheadPercent,
            dataCapGb: $this->dataCapGb !== null && $this->dataCapGb > 0 ? (int) $this->dataCapGb : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    #[Computed]
    public function pcResult(): array
    {
        return app(CalculatorService::class)->estimatePcReadiness(
            cpuTier: $this->cpuTier,
            gpuTier: $this->gpuTier,
            ramGb: (int) $this->ramGb,
            vramGb: (int) $this->vramGb,
            resolution: $this->resolution,
            rayTracing: $this->rayTracing,
            upscaler: $this->upscaler,
        );
    }

    /**
     * @return array<string, mixed>
     */
    #[Computed]
    public function editionResult(): array
    {
        $edition = $this->editions->firstWhere('id', $this->editionId) ?? $this->editions->first();
        if ($edition === null) {
            return [];
        }

        return app(CalculatorService::class)->calculateEditionValue(
            edition: $edition,
            currency: $this->currency,
            expectedPlayHours: (int) $this->expectedPlayHours,
        );
    }

    /**
     * @return array<string, mixed>
     */
    #[Computed]
    public function mapResult(): array
    {
        $location = $this->locations->firstWhere('id', $this->locationId);

        return app(CalculatorService::class)->calculateMapComparison(
            speedKmh: (float) $this->vehicleSpeedKmh,
            location: $location,
        );
    }

    public function render(): View
    {
        return view('livewire.interactive-calculators');
    }
}
