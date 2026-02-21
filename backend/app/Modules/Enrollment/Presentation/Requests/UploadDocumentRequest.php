<?php

namespace App\Modules\Enrollment\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp'],
            'document_type' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
