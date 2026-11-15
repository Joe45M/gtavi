<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\PlatformSpec;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PlatformSpec
 */
final class PlatformSpecResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'platform_name' => $this->platform_name,
            'slug' => $this->slug,
            'platform_family' => $this->platform_family,
            'target_resolution' => $this->target_resolution,
            'target_fps' => $this->target_fps,
            'ray_tracing_tier' => $this->ray_tracing_tier,
            'upscaling_tech' => $this->upscaling_tech,
            'estimated_download_gb' => $this->estimated_download_gb,
            'high_res_pack_gb' => $this->high_res_pack_gb,
            'total_projected_gb' => $this->totalSizeWithHighResGb(),
            'min_ssd_speed_mbps' => $this->min_ssd_speed_mbps,
            'cpu_requirement' => $this->cpu_requirement,
            'gpu_requirement' => $this->gpu_requirement,
            'ram_gb' => $this->ram_gb,
            'vram_gb' => $this->vram_gb,
            'release_window' => $this->release_window,
            'release_status' => $this->release_status,
            'notes' => $this->notes,
        ];
    }
}
