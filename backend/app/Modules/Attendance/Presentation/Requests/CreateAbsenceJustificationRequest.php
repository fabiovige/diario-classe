<?php

namespace App\Modules\Attendance\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAbsenceJustificationRequest extends FormRequest
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
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
            'document_path' => ['nullable', 'string', 'max:500'],
        ];
    }
}
