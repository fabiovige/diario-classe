<?php

namespace App\Modules\Assessment\Application\DTOs;

final readonly class RecordBulkGradesDTO
{
    /** @param array<int, array{student_id: int, numeric_value?: float|null, conceptual_value?: string|null, observations?: string|null}> $grades */
    public function __construct(
        public int $classGroupId,
        public int $teacherAssignmentId,
        public int $assessmentPeriodId,
        public int $assessmentInstrumentId,
        public array $grades,
    ) {}
}
