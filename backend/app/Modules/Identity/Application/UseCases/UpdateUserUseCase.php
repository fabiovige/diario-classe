<?php

namespace App\Modules\Identity\Application\UseCases;

use App\Models\User;
use App\Modules\Identity\Application\DTOs\UpdateUserDTO;

final class UpdateUserUseCase
{
    public function execute(UpdateUserDTO $dto): User
    {
        $user = User::findOrFail($dto->id);

        $user->update(array_filter([
            'name' => $dto->name,
            'email' => $dto->email,
            'cpf' => $dto->cpf,
            'status' => $dto->status,
            'role_id' => $dto->roleId,
            'school_id' => $dto->schoolId,
        ], fn ($value) => $value !== null));

        return $user->refresh();
    }
}
