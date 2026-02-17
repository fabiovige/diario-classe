<?php

namespace App\Modules\PeriodClosing\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestRectificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'period_closing_id' => ['required', 'integer', 'exists:period_closings,id'],
            'entity_type' => ['required', 'string', 'max:100'],
            'entity_id' => ['required', 'integer'],
            'field_changed' => ['required', 'string', 'max:100'],
            'old_value' => ['required', 'string'],
            'new_value' => ['required', 'string'],
            'justification' => ['required', 'string', 'max:1000'],
        ];
    }
}
