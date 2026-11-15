<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class ReleaseMilestone extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'milestone_date',
        'display_date_label',
        'category',
        'source_name',
        'source_url',
        'is_confirmed',
        'description',
        'impact_level',
        'sort_order',
    ];

    /**
     * @param  Builder<ReleaseMilestone>  $query
     * @return Builder<ReleaseMilestone>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('milestone_date');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'milestone_date' => 'date',
            'is_confirmed' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
