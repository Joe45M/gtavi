<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CalculateDownloadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'speed' => ['required', 'numeric', 'min:1', 'max:100000'],
            'unit' => ['required', 'string', Rule::in(['Mbps', 'MBps', 'Gbps'])],
            'size_gb' => ['required', 'numeric', 'min:1', 'max:1000'],
            'overhead_percent' => ['sometimes', 'numeric', 'min:0', 'max:40'],
            'data_cap_gb' => ['nullable', 'integer', 'min:1', 'max:50000'],
        ];
    }
}
