<?php

namespace App\Modules\Curriculum\Application\UseCases;

use App\Modules\Curriculum\Application\DTOs\CreateExperienceFieldDTO;
use App\Modules\Curriculum\Domain\Entities\ExperienceField;

final class CreateExperienceFieldUseCase
{
    public function execute(CreateExperienceFieldDTO $dto): ExperienceField
    {
        return ExperienceField::create([
            'name' => $dto->name,
            'code' => $dto->code,
            'active' => true,
        ]);
    }
}
