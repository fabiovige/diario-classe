<?php

namespace App\Modules\SchoolStructure\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateClassGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'shift_id' => ['required', 'integer', 'exists:shifts,id'],
            'name' => ['required', 'string', 'max:10'],
            'max_students' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }
}
