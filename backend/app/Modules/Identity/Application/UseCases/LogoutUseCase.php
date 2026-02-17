<?php

namespace App\Modules\Identity\Application\UseCases;

use App\Models\User;

final class LogoutUseCase
{
    public function execute(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
