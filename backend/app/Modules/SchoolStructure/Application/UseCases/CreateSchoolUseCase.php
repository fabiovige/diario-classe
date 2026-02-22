<?php

namespace App\Modules\SchoolStructure\Application\UseCases;

use App\Modules\SchoolStructure\Application\DTOs\CreateSchoolDTO;
use App\Modules\SchoolStructure\Domain\Entities\School;

final class CreateSchoolUseCase
{
    public function execute(CreateSchoolDTO $dto): School
    {
        return School::create([
            'name' => $dto->name,
            'inep_code' => $dto->inepCode,
            'type' => 'municipal',
            'address' => $dto->address,
            'phone' => $dto->phone,
            'email' => $dto->email,
        ]);
    }
}
