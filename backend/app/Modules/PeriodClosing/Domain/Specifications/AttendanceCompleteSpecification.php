<?php

namespace App\Modules\PeriodClosing\Domain\Specifications;

use App\Modules\Attendance\Domain\Entities\AttendanceRecord;

final class AttendanceCompleteSpecification
{
    public function isSatisfiedBy(int $classGroupId, int $teacherAssignmentId, string $startDate, string $endDate): bool
    {
        $totalDays = AttendanceRecord::where('class_group_id', $classGroupId)
            ->where('teacher_assignment_id', $teacherAssignmentId)
            ->whereBetween('date', [$startDate, $endDate])
            ->distinct('date')
            ->count('date');

        return $totalDays > 0;
    }
}
