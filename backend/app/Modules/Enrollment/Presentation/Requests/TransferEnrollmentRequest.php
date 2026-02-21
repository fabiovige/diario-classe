<?php

namespace App\Modules\Enrollment\Presentation\Requests;

use App\Modules\Enrollment\Domain\Enums\MovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::enum(MovementType::class)],
            'movement_date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:500'],
            'origin_school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'destination_school_id' => ['nullable', 'integer', 'exists:schools,id'],
        ];
    }
}
