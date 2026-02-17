<?php

namespace App\Modules\Enrollment\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'enrollment_date' => ['required', 'date'],
            'enrollment_number' => ['nullable', 'string', 'max:50'],
        ];
    }
}
