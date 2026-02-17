<?php

namespace App\Modules\AcademicCalendar\Presentation\Requests;

use App\Modules\AcademicCalendar\Domain\Enums\AssessmentPeriodStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssessmentPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'status' => ['sometimes', 'string', Rule::in(array_column(AssessmentPeriodStatus::cases(), 'value'))],
        ];
    }
}
