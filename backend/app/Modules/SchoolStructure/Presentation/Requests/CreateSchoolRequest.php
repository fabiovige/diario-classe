<?php

namespace App\Modules\SchoolStructure\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'inep_code' => ['nullable', 'string', 'size:8', 'unique:schools,inep_code'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
        ];
    }
}
