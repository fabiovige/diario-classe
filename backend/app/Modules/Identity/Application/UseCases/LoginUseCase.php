<?php

namespace App\Modules\Identity\Application\UseCases;

use App\Models\User;
use App\Modules\Identity\Application\DTOs\LoginDTO;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class LoginUseCase
{
    /** @return array{user: User, token: string} */
    public function execute(LoginDTO $dto): array
    {
        $user = User::where('email', $dto->email)->first();

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        if (! $user->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['Usuário inativo ou bloqueado.'],
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return [
            'user' => $user->load('role'),
            'token' => $token,
        ];
    }
}
