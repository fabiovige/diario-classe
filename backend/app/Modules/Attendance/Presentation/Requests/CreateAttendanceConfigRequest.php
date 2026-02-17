<?php

namespace App\Modules\Attendance\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAttendanceConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'consecutive_absences_alert' => ['sometimes', 'integer', 'min:1', 'max:30'],
            'monthly_absences_alert' => ['sometimes', 'integer', 'min:1', 'max:30'],
            'period_absence_percentage_alert' => ['sometimes', 'numeric', 'min:1', 'max:100'],
            'annual_minimum_frequency' => ['sometimes', 'numeric', 'min:1', 'max:100'],
        ];
    }
}
