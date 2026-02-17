<?php

namespace App\Modules\Identity\Presentation\Requests;

use App\Modules\Identity\Domain\Enums\RoleSlug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRoleRequest extends FormRequest
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
            'slug' => ['required', 'string', Rule::enum(RoleSlug::class), 'unique:roles,slug'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string'],
        ];
    }
}
