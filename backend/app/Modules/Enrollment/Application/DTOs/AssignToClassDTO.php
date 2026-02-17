<?php

namespace App\Modules\Enrollment\Application\DTOs;

final readonly class AssignToClassDTO
{
    public function __construct(
        public int $enrollmentId,
        public int $classGroupId,
        public string $startDate,
    ) {}
}
