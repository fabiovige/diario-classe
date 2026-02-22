<?php

namespace App\Modules\Curriculum\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveClassScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'slots' => ['required', 'array'],
            'slots.*.time_slot_id' => ['required', 'integer', 'exists:time_slots,id'],
            'slots.*.day_of_week' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }
}
