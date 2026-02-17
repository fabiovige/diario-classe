<?php

namespace App\Modules\People\Application\UseCases;

use App\Modules\People\Application\DTOs\CreateTeacherDTO;
use App\Modules\People\Domain\Entities\Teacher;

final class CreateTeacherUseCase
{
    public function execute(CreateTeacherDTO $dto): Teacher
    {
        return Teacher::create([
            'user_id' => $dto->userId,
            'school_id' => $dto->schoolId,
            'registration_number' => $dto->registrationNumber,
            'specialization' => $dto->specialization,
            'hire_date' => $dto->hireDate,
        ]);
    }
}
