<?php

namespace App\Modules\People\Presentation\Requests;

use App\Modules\People\Domain\Enums\Relationship;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachGuardianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'guardian_id' => ['required', 'integer', 'exists:guardians,id'],
            'relationship' => ['required', 'string', Rule::enum(Relationship::class)],
            'is_primary' => ['sometimes', 'boolean'],
            'can_pickup' => ['sometimes', 'boolean'],
        ];
    }
}
