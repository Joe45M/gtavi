<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\GameEdition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin GameEdition
 */
final class GameEditionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'tagline' => $this->tagline,
            'is_price_confirmed' => $this->is_price_confirmed,
            'pricing' => [
                'USD' => $this->price_usd,
                'GBP' => $this->price_gbp,
                'EUR' => $this->price_eur,
                'CAD' => $this->price_cad,
                'AUD' => $this->price_aud,
                'JPY' => $this->price_jpy,
            ],
            'estimated_size_gb' => $this->estimated_size_gb,
            'early_access_hours' => $this->early_access_hours,
            'online_bonus_credits' => $this->online_bonus_credits,
            'physical_collectibles' => $this->physical_collectibles ?? [],
            'digital_bonuses' => $this->digital_bonuses ?? [],
            'badge_text' => $this->badge_text,
            'disclaimer' => 'Independent projection for planning only; not affiliated with Rockstar Games or Take-Two Interactive.',
        ];
    }
}
