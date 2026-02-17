<?php

namespace App\Modules\SchoolStructure\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'inep_code' => ['sometimes', 'nullable', 'string', 'size:8', 'unique:schools,inep_code,'.$this->route('school')],
            'address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'email' => ['sometimes', 'nullable', 'email'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
