<?php

namespace App\Modules\Assessment\Presentation\Requests;

use App\Modules\Assessment\Domain\Enums\RecoveryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordRecoveryGradeRequest extends FormRequest
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
            'class_group_id' => ['required', 'integer', 'exists:class_groups,id'],
            'teacher_assignment_id' => ['required', 'integer', 'exists:teacher_assignments,id'],
            'assessment_period_id' => ['required', 'integer', 'exists:assessment_periods,id'],
            'assessment_instrument_id' => ['required', 'integer', 'exists:assessment_instruments,id'],
            'numeric_value' => ['nullable', 'numeric', 'min:0'],
            'conceptual_value' => ['nullable', 'string', 'max:10'],
            'recovery_type' => ['required', 'string', Rule::in(array_column(RecoveryType::cases(), 'value'))],
        ];
    }
}
