<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class GameEdition extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'tagline',
        'price_usd',
        'price_gbp',
        'price_eur',
        'price_cad',
        'price_aud',
        'price_jpy',
        'is_price_confirmed',
        'estimated_size_gb',
        'early_access_hours',
        'online_bonus_credits',
        'physical_collectibles',
        'digital_bonuses',
        'badge_text',
        'is_highlighted',
        'sort_order',
    ];

    /**
     * @param  Builder<GameEdition>  $query
     * @return Builder<GameEdition>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('price_usd');
    }

    public function priceForCurrency(string $currency): float
    {
        return match (strtoupper($currency)) {
            'GBP' => (float) $this->price_gbp,
            'EUR' => (float) $this->price_eur,
            'CAD' => (float) $this->price_cad,
            'AUD' => (float) $this->price_aud,
            'JPY' => (float) $this->price_jpy,
            default => (float) $this->price_usd,
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_usd' => 'float',
            'price_gbp' => 'float',
            'price_eur' => 'float',
            'price_cad' => 'float',
            'price_aud' => 'float',
            'price_jpy' => 'integer',
            'is_price_confirmed' => 'boolean',
            'estimated_size_gb' => 'integer',
            'early_access_hours' => 'integer',
            'online_bonus_credits' => 'integer',
            'physical_collectibles' => 'array',
            'digital_bonuses' => 'array',
            'is_highlighted' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
