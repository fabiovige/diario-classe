<?php

namespace App\Modules\People\Presentation\Requests;

use App\Modules\People\Domain\Enums\DisabilityType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'disability_type' => ['required_if:has_disability,true', 'nullable', Rule::enum(DisabilityType::class)],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
