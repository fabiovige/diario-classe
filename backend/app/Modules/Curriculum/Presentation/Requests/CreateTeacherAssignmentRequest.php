<?php

namespace App\Modules\Curriculum\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTeacherAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'class_group_id' => ['required', 'integer', 'exists:class_groups,id'],
            'curricular_component_id' => ['nullable', 'integer', 'exists:curricular_components,id'],
            'experience_field_id' => ['nullable', 'integer', 'exists:experience_fields,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
        ];
    }
}
