<?php

namespace App\Modules\People\Application\DTOs;

final readonly class CreateTeacherDTO
{
    public function __construct(
        public int $userId,
        public int $schoolId,
        public ?string $registrationNumber = null,
        public ?string $specialization = null,
        public ?string $hireDate = null,
    ) {}
}
