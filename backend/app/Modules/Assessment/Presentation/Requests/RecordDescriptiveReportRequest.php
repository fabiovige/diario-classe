<?php

namespace App\Modules\Assessment\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordDescriptiveReportRequest extends FormRequest
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
            'experience_field_id' => ['required', 'integer', 'exists:experience_fields,id'],
            'assessment_period_id' => ['required', 'integer', 'exists:assessment_periods,id'],
            'content' => ['required', 'string'],
        ];
    }
}
