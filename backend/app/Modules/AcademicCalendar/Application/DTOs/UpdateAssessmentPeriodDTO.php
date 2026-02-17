<?php

namespace App\Modules\AcademicCalendar\Application\DTOs;

final readonly class UpdateAssessmentPeriodDTO
{
    public function __construct(
        public int $id,
        public ?string $name = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
        public ?string $status = null,
    ) {}
}
