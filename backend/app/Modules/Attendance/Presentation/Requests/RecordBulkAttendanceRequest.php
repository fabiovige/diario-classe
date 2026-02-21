<?php

namespace App\Modules\Attendance\Presentation\Requests;

use App\Modules\Attendance\Domain\Enums\AttendanceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordBulkAttendanceRequest extends FormRequest
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
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'records.*.status' => ['required', 'string', Rule::in(array_column(AttendanceStatus::cases(), 'value'))],
            'records.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
