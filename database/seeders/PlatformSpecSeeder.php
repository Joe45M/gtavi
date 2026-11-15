<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PlatformSpec;
use Illuminate\Database\Seeder;

final class PlatformSpecSeeder extends Seeder
{
    public function run(): void
    {
        $specs = [
            [
                'platform_name' => 'PlayStation 5',
                'slug' => 'playstation-5',
                'platform_family' => 'console',
                'target_resolution' => 'Dynamic 1440p (Fidelity) / 1080p–1296p (Performance)',
                'target_fps' => '30 FPS Quality / Targeted 60 FPS',
                'ray_tracing_tier' => 'Ray-Traced Global Illumination (RTGI) + Hybrid Reflections',
                'upscaling_tech' => 'AMD FSR 2/3 Temporal Reconstruction',
                'estimated_download_gb' => 155,
                'high_res_pack_gb' => 0,
                'min_ssd_speed_mbps' => 5500,
                'cpu_requirement' => 'Custom 8-Core AMD Zen 2 @ 3.5 GHz',
                'gpu_requirement' => 'Custom AMD RDNA 2 (10.28 TFLOPS)',
                'ram_gb' => 16,
                'vram_gb' => 12,
                'release_window' => 'Primary Console Wave',
                'release_status' => 'confirmed_window',
                'notes' => 'Console launch hardware confirmed via official Rockstar announcement. Kraken decompression keeps base footprint lean compared to raw PC asset packs.',
                'sort_order' => 1,
            ],
            [
                'platform_name' => 'PlayStation 5 Pro',
                'slug' => 'playstation-5-pro',
                'platform_family' => 'console',
                'target_resolution' => 'Upscaled 4K (1440p–1800p Internal)',
                'target_fps' => '40 FPS Quality / 60 FPS PSSR Mode',
                'ray_tracing_tier' => 'Enhanced RTGI + Per-Pixel RT Reflections',
                'upscaling_tech' => 'PlayStation Spectral Super Resolution (PSSR)',
                'estimated_download_gb' => 165,
                'high_res_pack_gb' => 15,
                'min_ssd_speed_mbps' => 5500,
                'cpu_requirement' => 'Custom 8-Core AMD Zen 2 (High-Frequency Boost Mode)',
                'gpu_requirement' => 'Upgraded RDNA 3/4 Hybrid RT Compute (~16.7 TFLOPS)',
                'ram_gb' => 18,
                'vram_gb' => 14,
                'release_window' => 'Primary Console Wave',
                'release_status' => 'confirmed_window',
                'notes' => 'Ideal console hardware projection for crisp neon reflections and 60 FPS temporal stability without checkerboard shimmer.',
                'sort_order' => 2,
            ],
            [
                'platform_name' => 'Xbox Series X',
                'slug' => 'xbox-series-x',
                'platform_family' => 'console',
                'target_resolution' => 'Dynamic 1440p–1800p (Fidelity) / 1080p–1440p (Perf)',
                'target_fps' => '30 FPS Quality / Targeted 60 FPS',
                'ray_tracing_tier' => 'Ray-Traced Global Illumination + Ambient Occlusion',
                'upscaling_tech' => 'AMD FSR Temporal Upscaling',
                'estimated_download_gb' => 158,
                'high_res_pack_gb' => 0,
                'min_ssd_speed_mbps' => 2400,
                'cpu_requirement' => 'Custom 8-Core AMD Zen 2 @ 3.8 GHz',
                'gpu_requirement' => 'Custom AMD RDNA 2 (12.15 TFLOPS)',
                'ram_gb' => 16,
                'vram_gb' => 10,
                'release_window' => 'Primary Console Wave',
                'release_status' => 'confirmed_window',
                'notes' => 'Confirmed target console platform. BCPack hardware compression handles high-density coastal textures across internal NVMe expansion cards.',
                'sort_order' => 3,
            ],
            [
                'platform_name' => 'Xbox Series S',
                'slug' => 'xbox-series-s',
                'platform_family' => 'console',
                'target_resolution' => 'Dynamic 900p–1080p',
                'target_fps' => '30 FPS Locked Target',
                'ray_tracing_tier' => 'Software / Baked Probe GI Fallback',
                'upscaling_tech' => 'AMD FSR 2 Performance Mode',
                'estimated_download_gb' => 118,
                'high_res_pack_gb' => 0,
                'min_ssd_speed_mbps' => 2400,
                'cpu_requirement' => 'Custom 8-Core AMD Zen 2 @ 3.6 GHz',
                'gpu_requirement' => 'Custom AMD RDNA 2 (4.0 TFLOPS)',
                'ram_gb' => 10,
                'vram_gb' => 8,
                'release_window' => 'Primary Console Wave',
                'release_status' => 'confirmed_window',
                'notes' => 'Smart Delivery trims 4K mipmaps, reducing projected SSD consumption by ~25% compared to Series X and PS5.',
                'sort_order' => 4,
            ],
            [
                'platform_name' => 'PC Recommended (1440p60)',
                'slug' => 'pc-recommended-1440p',
                'platform_family' => 'pc',
                'target_resolution' => '2560 x 1440 (DLSS/FSR Quality)',
                'target_fps' => '60–85 FPS Projected',
                'ray_tracing_tier' => 'High RTGI + Selective Streetlight Reflections',
                'upscaling_tech' => 'NVIDIA DLSS 3.5+ / AMD FSR 3.1 / Intel XeSS',
                'estimated_download_gb' => 175,
                'high_res_pack_gb' => 25,
                'min_ssd_speed_mbps' => 3500,
                'cpu_requirement' => 'AMD Ryzen 7 5800X3D / Intel Core i7-13700K',
                'gpu_requirement' => 'RTX 4070 / RX 7800 XT (12+ GB VRAM)',
                'ram_gb' => 32,
                'vram_gb' => 12,
                'release_window' => 'Post-Console PC Wave (Projected +12–18 Mo)',
                'release_status' => 'projected_window',
                'notes' => 'DirectStorage 1.2 + GPU decompression projected for seamless high-speed jet and supercar asset streaming.',
                'sort_order' => 5,
            ],
            [
                'platform_name' => 'PC Ultra Vice Nocturne (4K RT)',
                'slug' => 'pc-ultra-4k-rt',
                'platform_family' => 'pc',
                'target_resolution' => '3840 x 2160 (4K DLSS/Frame Gen)',
                'target_fps' => '90–120+ FPS Projected',
                'ray_tracing_tier' => 'Full Path-Traced Reflections, Shadows & Volumetric Caustics',
                'upscaling_tech' => 'DLSS Ray Reconstruction + Frame Generation',
                'estimated_download_gb' => 185,
                'high_res_pack_gb' => 40,
                'min_ssd_speed_mbps' => 6500,
                'cpu_requirement' => 'AMD Ryzen 7 7800X3D / 9800X3D or Intel Core Ultra 9',
                'gpu_requirement' => 'RTX 4080 Super / RTX 4090 / RTX 5080 (16+ GB VRAM)',
                'ram_gb' => 32,
                'vram_gb' => 16,
                'release_window' => 'Post-Console PC Wave (Projected +12–18 Mo)',
                'release_status' => 'projected_window',
                'notes' => 'Projected enthusiast ceiling with uncompressed 4K foliage, car clear-coat shaders, and ultra NPC crowd budgets.',
                'sort_order' => 6,
            ],
        ];

        foreach ($specs as $spec) {
            PlatformSpec::query()->updateOrCreate(
                ['slug' => $spec['slug']],
                $spec
            );
        }
    }
}
