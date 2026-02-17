<?php

namespace App\Modules\Assessment\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordBulkGradesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'class_group_id' => ['required', 'integer', 'exists:class_groups,id'],
            'teacher_assignment_id' => ['required', 'integer', 'exists:teacher_assignments,id'],
            'assessment_period_id' => ['required', 'integer', 'exists:assessment_periods,id'],
            'assessment_instrument_id' => ['required', 'integer', 'exists:assessment_instruments,id'],
            'grades' => ['required', 'array', 'min:1'],
            'grades.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'grades.*.numeric_value' => ['nullable', 'numeric', 'min:0'],
            'grades.*.conceptual_value' => ['nullable', 'string', 'max:10'],
            'grades.*.observations' => ['nullable', 'string', 'max:500'],
        ];
    }
}
