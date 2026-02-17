<?php

namespace App\Modules\PeriodClosing\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidatePeriodClosingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'approve' => ['required', 'boolean'],
            'rejection_reason' => ['required_if:approve,false', 'nullable', 'string', 'max:1000'],
        ];
    }
}
