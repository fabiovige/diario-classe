<?php

namespace App\Modules\Identity\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
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
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'cpf' => ['nullable', 'string', 'size:11', 'unique:users,cpf'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'school_id' => ['nullable', 'integer'],
        ];
    }
}
