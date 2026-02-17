<?php

namespace App\Modules\ClassRecord\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateLessonRecordRequest extends FormRequest
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
            'date' => ['required', 'date'],
            'content' => ['required', 'string'],
            'methodology' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'class_count' => ['sometimes', 'integer', 'min:1', 'max:10'],
        ];
    }
}
