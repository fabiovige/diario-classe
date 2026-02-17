<?php

namespace App\Modules\ClassRecord\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'content' => ['sometimes', 'string'],
            'methodology' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'class_count' => ['sometimes', 'integer', 'min:1', 'max:10'],
        ];
    }
}
