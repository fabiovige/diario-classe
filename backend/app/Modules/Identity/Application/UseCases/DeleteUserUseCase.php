<?php

namespace App\Modules\Identity\Application\UseCases;

use App\Models\User;
use App\Modules\Identity\Domain\Enums\UserStatus;

final class DeleteUserUseCase
{
    public function execute(int $id): void
    {
        $user = User::findOrFail($id);
        $user->update(['status' => UserStatus::Inactive->value]);
        $user->tokens()->delete();
    }
}
