<?php

namespace App\Modules\Enrollment\Application\DTOs;

use App\Modules\Enrollment\Domain\Entities\ClassAssignment;

final readonly class AssignToClassResult
{
    /** @param array<string> $warnings */
    public function __construct(
        public ClassAssignment $assignment,
        public array $warnings = [],
    ) {}
}
