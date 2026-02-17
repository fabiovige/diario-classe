<?php

namespace App\Modules\Identity\Application\UseCases;

use App\Models\User;
use App\Modules\Identity\Application\DTOs\CreateUserDTO;

final class CreateUserUseCase
{
    public function execute(CreateUserDTO $dto): User
    {
        return User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'cpf' => $dto->cpf,
            'password' => $dto->password,
            'role_id' => $dto->roleId,
            'school_id' => $dto->schoolId,
        ]);
    }
}
