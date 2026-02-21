<?php

namespace App\Modules\Enrollment\Application\DTOs;

final readonly class CreateEnrollmentDTO
{
    public function __construct(
        public int $studentId,
        public int $academicYearId,
        public int $schoolId,
        public string $enrollmentDate,
        public string $enrollmentType = 'new_enrollment',
        public ?string $enrollmentNumber = null,
    ) {}
}
