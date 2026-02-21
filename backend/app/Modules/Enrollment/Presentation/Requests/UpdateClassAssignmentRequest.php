<?php

namespace App\Modules\Enrollment\Presentation\Requests;

use App\Modules\Enrollment\Domain\Enums\ClassAssignmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateClassAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'class_group_id' => ['sometimes', 'integer', 'exists:class_groups,id'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date'],
            'status' => ['sometimes', new Enum(ClassAssignmentStatus::class)],
        ];
    }
}
