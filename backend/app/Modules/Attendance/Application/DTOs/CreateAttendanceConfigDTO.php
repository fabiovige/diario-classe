<?php

namespace App\Modules\Attendance\Application\DTOs;

final readonly class CreateAttendanceConfigDTO
{
    public function __construct(
        public int $schoolId,
        public int $academicYearId,
        public int $consecutiveAbsencesAlert = 5,
        public int $monthlyAbsencesAlert = 10,
        public float $periodAbsencePercentageAlert = 25.00,
        public float $annualMinimumFrequency = 75.00,
    ) {}
}
