<?php

namespace App\Modules\AcademicCalendar\Presentation\Requests;

use App\Modules\AcademicCalendar\Domain\Enums\AssessmentPeriodType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAssessmentPeriodRequest extends FormRequest
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
            'type' => ['required', 'string', Rule::in(array_column(AssessmentPeriodType::cases(), 'value'))],
            'number' => ['required', 'integer', 'min:1', 'max:4'],
            'name' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ];
    }
}
