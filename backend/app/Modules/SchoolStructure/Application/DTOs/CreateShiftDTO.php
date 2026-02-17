<?php

namespace App\Modules\SchoolStructure\Application\DTOs;

final readonly class CreateShiftDTO
{
    public function __construct(
        public int $schoolId,
        public string $name,
        public string $startTime,
        public string $endTime,
    ) {}
}
