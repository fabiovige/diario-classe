<?php

namespace App\Modules\Enrollment\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignToClassRequest extends FormRequest
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
            'start_date' => ['required', 'date'],
        ];
    }
}
