<?php

namespace App\Modules\Curriculum\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'teacher_id' => ['sometimes', 'integer', 'exists:teachers,id'],
            'class_group_id' => ['sometimes', 'integer', 'exists:class_groups,id'],
            'curricular_component_id' => ['sometimes', 'nullable', 'integer', 'exists:curricular_components,id'],
            'experience_field_id' => ['sometimes', 'nullable', 'integer', 'exists:experience_fields,id'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
