<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit posts') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Post|null $post */
        $post = $this->route('post');

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'min:5', 'max:180'],
            'slug' => [
                'nullable',
                'string',
                'max:200',
                Rule::unique('posts', 'slug')->ignore($post?->id),
            ],
            'subtitle' => ['nullable', 'string', 'max:220'],
            'excerpt' => ['required', 'string', 'min:20', 'max:500'],
            'content_blocks' => ['required', 'array', 'min:1'],
            'content_blocks.*.type' => ['required', 'string', Rule::in([
                'heading',
                'paragraph',
                'callout',
                'spec_table',
                'faq',
                'calculator_embed',
                'quote',
                'code',
                'stat_grid',
            ])],
            'featured_image_label' => ['nullable', 'string', 'max:120'],
            'featured_image_gradient' => ['nullable', 'string', 'max:120'],
            'author_name' => ['nullable', 'string', 'max:100'],
            'author_role' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', Rule::in(['draft', 'published', 'scheduled'])],
            'verification_badge' => ['required', 'string', Rule::in([
                'confirmed',
                'official_filing',
                'technical_projection',
                'community_speculation',
            ])],
            'is_featured' => ['sometimes', 'boolean'],
            'focus_keyword' => ['nullable', 'string', 'max:120'],
            'meta_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:160'],
            'og_description' => ['nullable', 'string', 'max:320'],
            'schema_type' => ['required', 'string', Rule::in(['BlogPosting', 'NewsArticle', 'TechArticle', 'FAQPage'])],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'editor_note' => ['nullable', 'string', 'max:180'],
        ];
    }
}
