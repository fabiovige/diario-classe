<?php

namespace App\Modules\People\Presentation\Requests;

use App\Modules\People\Domain\Enums\DisabilityType;
use App\Modules\People\Domain\Enums\Gender;
use App\Modules\People\Domain\Enums\RaceColor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'social_name' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'string', Rule::enum(Gender::class)],
            'race_color' => ['sometimes', 'string', Rule::enum(RaceColor::class)],
            'cpf' => ['nullable', 'string', 'size:11', 'unique:students,cpf'],
            'rg' => ['nullable', 'string', 'max:20'],
            'sus_number' => ['nullable', 'string', 'max:15'],
            'nis_number' => ['nullable', 'string', 'max:11'],
            'birth_city' => ['nullable', 'string', 'max:100'],
            'birth_state' => ['nullable', 'string', 'size:2'],
            'nationality' => ['sometimes', 'string', 'max:50'],
            'medical_notes' => ['nullable', 'string'],
            'has_disability' => ['sometimes', 'boolean'],
            'disability_type' => ['required_if:has_disability,true', 'nullable', Rule::enum(DisabilityType::class)],
        ];
    }
}
