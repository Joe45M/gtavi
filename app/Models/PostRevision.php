<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PostRevision extends Model
{
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'title',
        'content_blocks',
        'seo_score',
        'editor_note',
        'created_at',
    ];

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content_blocks' => 'array',
            'seo_score' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
