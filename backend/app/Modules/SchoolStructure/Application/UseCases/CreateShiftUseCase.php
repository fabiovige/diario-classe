<?php

namespace App\Modules\SchoolStructure\Application\UseCases;

use App\Modules\SchoolStructure\Application\DTOs\CreateShiftDTO;
use App\Modules\SchoolStructure\Domain\Entities\Shift;

final class CreateShiftUseCase
{
    public function execute(CreateShiftDTO $dto): Shift
    {
        return Shift::create([
            'school_id' => $dto->schoolId,
            'name' => $dto->name,
            'start_time' => $dto->startTime,
            'end_time' => $dto->endTime,
        ]);
    }
}
