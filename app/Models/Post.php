<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Post extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'subtitle',
        'excerpt',
        'content_blocks',
        'rendered_html',
        'featured_image_label',
        'featured_image_gradient',
        'author_name',
        'author_role',
        'status',
        'verification_badge',
        'is_featured',
        'reading_time_minutes',
        'word_count',
        'focus_keyword',
        'meta_title',
        'meta_description',
        'canonical_url',
        'og_title',
        'og_description',
        'schema_type',
        'faq_items',
        'seo_score',
        'readability_score',
        'view_count',
        'published_at',
    ];

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * @return HasMany<PostRevision, $this>
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(PostRevision::class)->orderByDesc('created_at');
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where(function (Builder $builder): void {
                $builder->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function verificationLabel(): string
    {
        return match ($this->verification_badge) {
            'confirmed' => 'Confirmed Fact',
            'official_filing' => 'Official Filing',
            'technical_projection' => 'Technical Projection',
            default => 'Community Analysis',
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content_blocks' => 'array',
            'faq_items' => 'array',
            'is_featured' => 'boolean',
            'reading_time_minutes' => 'integer',
            'word_count' => 'integer',
            'seo_score' => 'integer',
            'readability_score' => 'integer',
            'view_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }
}
