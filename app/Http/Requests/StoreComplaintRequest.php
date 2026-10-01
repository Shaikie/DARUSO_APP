<?php

namespace App\Http\Requests;

use App\Enums\ComplaintCategory;
use App\Rules\SafeUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComplaintRequest extends FormRequest
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
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['required', 'string', 'min:20'],
            'category' => ['required', Rule::in(ComplaintCategory::values())],
            'attachment' => [
                'nullable',
                'file',
                'mimes:'.implode(',', config('daruso.uploads.mimes')),
                new SafeUpload((int) config('daruso.uploads.max_size_kb')),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'description.min' => 'Please describe your complaint in at least 20 characters.',
            'title.min' => 'Please give your complaint a short title (at least 5 characters).',
        ];
    }
}
