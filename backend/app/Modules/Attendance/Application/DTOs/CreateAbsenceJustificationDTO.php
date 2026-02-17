<?php

namespace App\Modules\Attendance\Application\DTOs;

final readonly class CreateAbsenceJustificationDTO
{
    public function __construct(
        public int $studentId,
        public string $startDate,
        public string $endDate,
        public string $reason,
        public ?string $documentPath = null,
    ) {}
}
