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
            'end_date' => ['sometimes', 'date'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
