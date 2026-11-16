<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class GenerateAiPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create posts') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'mode' => ['required', 'string', Rule::in(['news', 'evergreen', 'both'])],
            'topic' => ['nullable', 'string', 'max:180'],
            'status' => ['required', 'string', Rule::in(['published', 'draft'])],
        ];
    }
}
