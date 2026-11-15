<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GameEdition;
use App\Models\MapLocation;

final readonly class CalculatorService
{
    /**
     * Calculate GTA VI download & pre-load durations with protocol overhead and ISP cap impact.
     *
     * @return array<string, mixed>
     */
    public function calculateDownloadTime(
        float $speedValue,
        string $unit,
        float $sizeGb,
        float $overheadPercent = 8.0,
        ?int $dataCapGb = null,
    ): array {
        $clampedSpeed = max(1.0, $speedValue);
        $clampedSizeGb = max(1.0, $sizeGb);
        $clampedOverhead = max(0.0, min(40.0, $overheadPercent));

        $rawMbps = match ($unit) {
            'MBps' => $clampedSpeed * 8.0,
            'Gbps' => $clampedSpeed * 1000.0,
            default => $clampedSpeed,
        };

        $efficiencyFactor = 1.0 - ($clampedOverhead / 100.0);
        $effectiveMbps = max(0.5, $rawMbps * $efficiencyFactor);
        $effectiveMbPerSec = $effectiveMbps / 8.0;

        // Binary-decimal store discrepancy buffer (~1000 MB per GB over wire)
        $totalMegabytes = $clampedSizeGb * 1000.0;
        $totalSeconds = (int) ceil($totalMegabytes / $effectiveMbPerSec);

        $tiers = [];
        foreach ([
            ['label' => 'Rural DSL / Basic (25 Mbps)', 'mbps' => 25.0],
            ['label' => 'Standard Broadband (100 Mbps)', 'mbps' => 100.0],
            ['label' => 'Cable / 5G Home (300 Mbps)', 'mbps' => 300.0],
            ['label' => 'Gigabit Fiber (1000 Mbps)', 'mbps' => 1000.0],
        ] as $tier) {
            $tierMbPerSec = ($tier['mbps'] * $efficiencyFactor) / 8.0;
            $tierSeconds = (int) ceil($totalMegabytes / $tierMbPerSec);
            $tiers[] = [
                'label' => $tier['label'],
                'mbps' => $tier['mbps'],
                'duration' => $this->formatSeconds($tierSeconds),
                'seconds' => $tierSeconds,
            ];
        }

        $dataCapPercent = ($dataCapGb !== null && $dataCapGb > 0)
            ? round(($clampedSizeGb / $dataCapGb) * 100, 1)
            : null;

        return [
            'raw_mbps' => round($rawMbps, 1),
            'effective_mbps' => round($effectiveMbps, 1),
            'effective_mb_per_sec' => round($effectiveMbPerSec, 2),
            'total_seconds' => $totalSeconds,
            'formatted_duration' => $this->formatSeconds($totalSeconds),
            'recommended_preload_hours' => max(2, (int) ceil(($totalSeconds / 3600) * 1.35)),
            'recommended_free_ssd_gb' => (int) ceil($clampedSizeGb * 1.25),
            'data_cap_percent' => $dataCapPercent,
            'tiers' => $tiers,
        ];
    }

    /**
     * Estimate PC hardware readiness and projected RAGE 9 frame-rate bands.
     *
     * @return array<string, mixed>
     */
    public function estimatePcReadiness(
        string $cpuTier,
        string $gpuTier,
        int $ramGb,
        int $vramGb,
        string $resolution,
        bool $rayTracing,
        string $upscaler,
    ): array {
        $cpuScore = match ($cpuTier) {
            'entry' => 45,
            'mid' => 70,
            'high' => 88,
            'enthusiast' => 100,
            default => 70,
        };

        $gpuScore = match ($gpuTier) {
            'entry' => 42,
            'mid' => 68,
            'high' => 86,
            'enthusiast' => 100,
            default => 68,
        };

        $resPenalty = match ($resolution) {
            '1080p' => 0,
            '1440p' => 14,
            '4k' => 30,
            default => 14,
        };

        $rtPenalty = $rayTracing ? 18 : 0;
        $upscalerBoost = match ($upscaler) {
            'dlss_fg' => 24,
            'dlss_quality', 'fsr_quality' => 14,
            'native' => 0,
            default => 12,
        };

        $vramRequired = match ($resolution) {
            '4k' => $rayTracing ? 16 : 12,
            '1440p' => $rayTracing ? 12 : 10,
            default => $rayTracing ? 10 : 8,
        };

        $ramPenalty = $ramGb < 16 ? 25 : ($ramGb < 32 && $rayTracing ? 5 : 0);
        $vramPenalty = $vramGb < $vramRequired ? ($vramRequired - $vramGb) * 6 : 0;

        $baseComposite = ($gpuScore * 0.62) + ($cpuScore * 0.38);
        $rawReadiness = (int) round(max(15, min(100, $baseComposite - ($resPenalty * 0.5) - ($rtPenalty * 0.4) - $ramPenalty - $vramPenalty + ($upscalerBoost * 0.45))));

        $avgFps = (int) round(max(24, min(144, (($baseComposite - $resPenalty - $rtPenalty + $upscalerBoost) * 0.85) - ($vramPenalty * 0.7))));
        $minFps = max(18, (int) round($avgFps * 0.74));
        $maxFps = (int) round($avgFps * 1.24);

        $bottleneck = 'Balanced Configuration';
        if ($ramGb < 16) {
            $bottleneck = 'System RAM (< 16 GB limits streaming)';
        } elseif ($vramGb < $vramRequired) {
            $bottleneck = "GPU VRAM ({$vramGb} GB vs {$vramRequired} GB target)";
        } elseif ($cpuScore + 15 < $gpuScore) {
            $bottleneck = 'CPU Simulation & Crowd Threading';
        } elseif ($gpuScore + 12 < $cpuScore) {
            $bottleneck = 'GPU Rasterization & Shader Throughput';
        }

        $preset = match (true) {
            $rawReadiness >= 85 => 'Ultra Leonida + RT Reflections',
            $rawReadiness >= 68 => 'High Vice Preset (Balanced RT)',
            $rawReadiness >= 48 => 'Medium-High Performance Preset',
            default => 'Optimized 1080p Console-Parity Preset',
        };

        return [
            'readiness_score' => $rawReadiness,
            'tier_label' => match (true) {
                $rawReadiness >= 85 => 'Vice Ultra Ready',
                $rawReadiness >= 68 => 'Smooth 60+ FPS Capable',
                $rawReadiness >= 48 => 'Console-Parity Viable',
                default => 'Upgrade Recommended',
            },
            'estimated_fps' => [
                'min' => $minFps,
                'avg' => $avgFps,
                'max' => $maxFps,
            ],
            'vram_required_gb' => $vramRequired,
            'vram_headroom_gb' => $vramGb - $vramRequired,
            'bottleneck' => $bottleneck,
            'recommended_preset' => $preset,
        ];
    }

    /**
     * Evaluate edition cost-per-hour and currency value metrics.
     *
     * @return array<string, mixed>
     */
    public function calculateEditionValue(GameEdition $edition, string $currency, int $expectedPlayHours): array
    {
        $hours = max(10, $expectedPlayHours);
        $currencyUpper = strtoupper($currency);
        $price = $edition->priceForCurrency($currencyUpper);

        $symbol = match ($currencyUpper) {
            'GBP' => '£',
            'EUR' => '€',
            'CAD' => 'CA$',
            'AUD' => 'A$',
            'JPY' => '¥',
            default => '$',
        };

        $formattedPrice = $currencyUpper === 'JPY'
            ? $symbol.number_format($price, 0)
            : $symbol.number_format($price, 2);

        $costPerHour = $price / $hours;
        $formattedCostPerHour = $currencyUpper === 'JPY'
            ? $symbol.number_format($costPerHour, 0)
            : $symbol.number_format($costPerHour, 2);

        $digitalCount = count($edition->digital_bonuses ?? []);
        $physicalCount = count($edition->physical_collectibles ?? []);

        return [
            'edition_name' => $edition->name,
            'currency' => $currencyUpper,
            'formatted_price' => $formattedPrice,
            'formatted_cost_per_hour' => $formattedCostPerHour,
            'cost_per_hour_raw' => round($costPerHour, 2),
            'early_access_hours' => $edition->early_access_hours,
            'online_bonus_credits' => $edition->online_bonus_credits,
            'estimated_size_gb' => $edition->estimated_size_gb,
            'bonus_items_count' => $digitalCount + $physicalCount,
            'value_verdict' => match (true) {
                $edition->early_access_hours >= 72 && $physicalCount > 0 => 'Collector Showcase Tier',
                $edition->early_access_hours > 0 => 'Best Launch-Week Value',
                default => 'Optimal Pure Cost-Per-Hour',
            },
        ];
    }

    /**
     * Compare State of Leonida projected scale against historic GTA maps and calculate crossing times.
     *
     * @return array<string, mixed>
     */
    public function calculateMapComparison(float $speedKmh, ?MapLocation $location = null): array
    {
        $speed = max(10.0, min(450.0, $speedKmh));
        $leonidaArea = 125.0;
        $losSantosArea = 75.8;
        $viceCity1986Area = 9.1;

        // Approximate diagonal transit distance across projected Leonida (~14.8 km)
        $leonidaCrossingKm = 14.8;
        $crossingMinutes = round(($leonidaCrossingKm / $speed) * 60, 1);

        $regionMinutes = null;
        if ($location !== null) {
            $regionDiameterKm = sqrt($location->estimated_area_sq_km) * 1.15;
            $regionMinutes = round(($regionDiameterKm / $speed) * 60, 1);
        }

        return [
            'leonida_area_sq_km' => $leonidaArea,
            'los_santos_area_sq_km' => $losSantosArea,
            'vice_city_1986_area_sq_km' => $viceCity1986Area,
            'multiplier_vs_gta5' => round($leonidaArea / $losSantosArea, 2),
            'multiplier_vs_vice_1986' => round($leonidaArea / $viceCity1986Area, 1),
            'leonida_crossing_minutes' => $crossingMinutes,
            'selected_region_name' => $location?->name,
            'selected_region_minutes' => $regionMinutes,
        ];
    }

    private function formatSeconds(int $totalSeconds): string
    {
        $hours = intdiv($totalSeconds, 3600);
        $minutes = intdiv($totalSeconds % 3600, 60);
        $seconds = $totalSeconds % 60;

        if ($hours > 0) {
            return sprintf('%dh %02dm %02ds', $hours, $minutes, $seconds);
        }

        if ($minutes > 0) {
            return sprintf('%dm %02ds', $minutes, $seconds);
        }

        return sprintf('%ds', $seconds);
    }
}
