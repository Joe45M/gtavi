<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Post
 */
final class PostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'excerpt' => $this->excerpt,
            'url' => url('/gta/'.$this->slug),
            'category' => $this->whenLoaded('category', fn (): array => [
                'name' => $this->category->name,
                'slug' => $this->category->slug,
                'accent_color' => $this->category->accent_color,
            ]),
            'tags' => $this->whenLoaded('tags', fn (): array => $this->tags->map(fn ($tag): array => [
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])->all()),
            'verification_badge' => $this->verification_badge,
            'verification_label' => $this->verificationLabel(),
            'reading_time_minutes' => $this->reading_time_minutes,
            'word_count' => $this->word_count,
            'meta' => [
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'schema_type' => $this->schema_type,
            ],
            'published_at' => $this->published_at?->toIso8601String(),
            'disclaimer' => 'Independent fan & telemetry publication. Not affiliated with Rockstar Games or Take-Two Interactive.',
        ];
    }
}
