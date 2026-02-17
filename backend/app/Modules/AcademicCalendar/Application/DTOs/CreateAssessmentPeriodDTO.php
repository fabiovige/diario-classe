<?php

namespace App\Modules\AcademicCalendar\Application\DTOs;

final readonly class CreateAssessmentPeriodDTO
{
    public function __construct(
        public int $academicYearId,
        public string $type,
        public int $number,
        public string $name,
        public string $startDate,
        public string $endDate,
    ) {}
}
