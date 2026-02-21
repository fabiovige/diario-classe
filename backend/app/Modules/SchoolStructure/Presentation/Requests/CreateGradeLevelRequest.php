<?php

namespace App\Modules\SchoolStructure\Presentation\Requests;

use App\Modules\SchoolStructure\Domain\Enums\GradeLevelType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateGradeLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', Rule::enum(GradeLevelType::class)],
            'order' => ['required', 'integer', 'min:1'],
        ];
    }
}
