<?php

namespace App\Modules\Curriculum\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateExperienceFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:experience_fields,name'],
            'code' => ['nullable', 'string', 'max:20'],
        ];
    }
}
