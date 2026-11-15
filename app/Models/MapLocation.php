<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class MapLocation extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'region_code',
        'real_world_inspiration',
        'biome_type',
        'estimated_area_sq_km',
        'key_landmarks',
        'vehicle_types',
        'description',
        'verification_status',
        'sort_order',
    ];

    /**
     * @param  Builder<MapLocation>  $query
     * @return Builder<MapLocation>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estimated_area_sq_km' => 'float',
            'key_landmarks' => 'array',
            'vehicle_types' => 'array',
            'sort_order' => 'integer',
        ];
    }
}
