<?php

namespace App\Modules\People\Application\UseCases;

use App\Modules\People\Application\DTOs\AttachGuardianDTO;
use App\Modules\People\Domain\Entities\Student;

final class AttachGuardianUseCase
{
    public function execute(AttachGuardianDTO $dto): void
    {
        $student = Student::findOrFail($dto->studentId);

        $student->guardians()->attach($dto->guardianId, [
            'relationship' => $dto->relationship,
            'is_primary' => $dto->isPrimary,
            'can_pickup' => $dto->canPickup,
        ]);
    }
}
