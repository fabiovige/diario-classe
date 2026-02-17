<?php

namespace App\Modules\Identity\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'email' => ['sometimes', 'email', 'unique:users,email,'.$this->route('user')],
            'cpf' => ['sometimes', 'nullable', 'string', 'size:11', 'unique:users,cpf,'.$this->route('user')],
            'status' => ['sometimes', 'string', 'in:active,inactive,blocked'],
            'role_id' => ['sometimes', 'nullable', 'integer', 'exists:roles,id'],
            'school_id' => ['sometimes', 'nullable', 'integer'],
        ];
    }
}
