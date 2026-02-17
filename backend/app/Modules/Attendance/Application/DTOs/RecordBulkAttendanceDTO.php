<?php

namespace App\Modules\Attendance\Application\DTOs;

final readonly class RecordBulkAttendanceDTO
{
    /** @param array<int, array{student_id: int, status: string}> $records */
    public function __construct(
        public int $classGroupId,
        public int $teacherAssignmentId,
        public string $date,
        public array $records,
    ) {}
}
