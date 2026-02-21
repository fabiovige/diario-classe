<?php

namespace App\Modules\Enrollment\Application\DTOs;

use App\Modules\Enrollment\Domain\Entities\Enrollment;

final readonly class CreateEnrollmentResult
{
    /** @param array<string> $warnings */
    public function __construct(
        public Enrollment $enrollment,
        public array $warnings = [],
    ) {}
}
