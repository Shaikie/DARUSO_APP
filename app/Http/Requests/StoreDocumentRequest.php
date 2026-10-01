<?php

namespace App\Http\Requests;

use App\Enums\DocumentCategory;
use App\Enums\DocumentVisibility;
use App\Rules\SafeUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Document upload validation.
 *
 * MIME validation uses `mimes`, which inspects file contents rather than trusting
 * the client-supplied extension or type header. The allowed list excludes
 * executable and script formats.
 */
class StoreDocumentRequest extends FormRequest
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
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', Rule::in(DocumentCategory::values())],
            'visibility' => ['required', Rule::in(DocumentVisibility::values())],

            // A single upload may arrive as one file or as an array of files. Both
            // shapes are validated so no upload can slip through unvalidated.
            'file' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'file',
                // Extension/type allow-list plus a content inspection pass.
                'mimes:'.implode(',', config('daruso.uploads.mimes')),
                new SafeUpload((int) config('daruso.uploads.max_size_kb')),
            ],

            // Optional link to the version being replaced. Guarded by a Rule so
            // the superseding document can only point at a real record.
            'supersedes_id' => ['nullable', 'integer', Rule::exists('documents', 'id')],
            'file.*' => [
                'file',
                'mimes:'.implode(',', config('daruso.uploads.mimes')),
                new SafeUpload((int) config('daruso.uploads.max_size_kb')),
            ],

            // Scope columns are required when the visibility implies them.
            'ministry_id' => [
                Rule::requiredIf($this->input('visibility') === DocumentVisibility::Ministry->value),
                'nullable',
                Rule::exists('ministries', 'id'),
            ],
            'committee_id' => [
                Rule::requiredIf($this->input('visibility') === DocumentVisibility::Committee->value),
                'nullable',
                Rule::exists('committees', 'id'),
            ],
            'group_id' => [
                Rule::requiredIf($this->input('visibility') === DocumentVisibility::Group->value),
                'nullable',
                'integer',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Select a file to upload.',
            'file.mimes' => 'The document must be one of: '.implode(', ', config('daruso.uploads.mimes')).'.',
            'file.max' => 'The document may not be larger than '.config('daruso.uploads.max_size_kb').' KB.',
            'file.*.mimes' => 'The document must be one of: '.implode(', ', config('daruso.uploads.mimes')).'.',
            'file.*.max' => 'The document may not be larger than '.config('daruso.uploads.max_size_kb').' KB.',
            'ministry_id.required' => 'Select the ministry this document belongs to.',
            'committee_id.required' => 'Select the committee this document belongs to.',
        ];
    }
}
