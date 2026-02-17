<?php

namespace App\Modules\SchoolStructure\Application\DTOs;

final readonly class CreateAcademicYearDTO
{
    public function __construct(
        public int $schoolId,
        public int $year,
        public string $startDate,
        public string $endDate,
        public string $status = 'planning',
    ) {}
}
