<?php

namespace App\Modules\SchoolStructure\Application\DTOs;

final readonly class CreateClassGroupDTO
{
    public function __construct(
        public int $academicYearId,
        public int $gradeLevelId,
        public int $shiftId,
        public string $name,
        public int $maxStudents = 30,
    ) {}
}
