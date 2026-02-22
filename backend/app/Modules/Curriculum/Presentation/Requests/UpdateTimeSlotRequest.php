<?php

namespace App\Modules\Curriculum\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'shift_id' => ['sometimes', 'integer', 'exists:shifts,id'],
            'number' => ['sometimes', 'integer', 'min:1', 'max:10'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i'],
            'type' => ['sometimes', 'string', 'in:class,break'],
        ];
    }
}
