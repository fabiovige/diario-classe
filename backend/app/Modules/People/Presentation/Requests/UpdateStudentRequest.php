<?php

namespace App\Modules\People\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'social_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'birth_date' => ['sometimes', 'date', 'before:today'],
            'gender' => ['sometimes', 'string'],
            'race_color' => ['sometimes', 'string'],
            'cpf' => ['sometimes', 'nullable', 'string', 'size:11', 'unique:students,cpf,'.$this->route('student')],
            'medical_notes' => ['sometimes', 'nullable', 'string'],
            'has_disability' => ['sometimes', 'boolean'],
            'disability_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
