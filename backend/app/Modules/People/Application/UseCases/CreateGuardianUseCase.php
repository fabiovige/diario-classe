<?php

namespace App\Modules\People\Application\UseCases;

use App\Modules\People\Application\DTOs\CreateGuardianDTO;
use App\Modules\People\Domain\Entities\Guardian;

final class CreateGuardianUseCase
{
    public function execute(CreateGuardianDTO $dto): Guardian
    {
        return Guardian::create([
            'name' => $dto->name,
            'cpf' => $dto->cpf,
            'rg' => $dto->rg,
            'phone' => $dto->phone,
            'phone_secondary' => $dto->phoneSecondary,
            'email' => $dto->email,
            'address' => $dto->address,
            'occupation' => $dto->occupation,
        ]);
    }
}
