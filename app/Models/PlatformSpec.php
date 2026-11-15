<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class PlatformSpec extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'platform_name',
        'slug',
        'platform_family',
        'target_resolution',
        'target_fps',
        'ray_tracing_tier',
        'upscaling_tech',
        'estimated_download_gb',
        'high_res_pack_gb',
        'min_ssd_speed_mbps',
        'cpu_requirement',
        'gpu_requirement',
        'ram_gb',
        'vram_gb',
        'release_window',
        'release_status',
        'notes',
        'sort_order',
    ];

    /**
     * @param  Builder<PlatformSpec>  $query
     * @return Builder<PlatformSpec>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('platform_name');
    }

    public function totalSizeWithHighResGb(): int
    {
        return $this->estimated_download_gb + $this->high_res_pack_gb;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estimated_download_gb' => 'integer',
            'high_res_pack_gb' => 'integer',
            'min_ssd_speed_mbps' => 'integer',
            'ram_gb' => 'integer',
            'vram_gb' => 'integer',
            'sort_order' => 'integer',
        ];
    }
}
