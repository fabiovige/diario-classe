<?php

namespace App\Modules\PeriodClosing\Domain\Specifications;

use App\Modules\ClassRecord\Domain\Entities\LessonRecord;

final class LessonRecordsCompleteSpecification
{
    public function isSatisfiedBy(int $classGroupId, int $teacherAssignmentId, string $startDate, string $endDate): bool
    {
        $totalDays = LessonRecord::where('class_group_id', $classGroupId)
            ->where('teacher_assignment_id', $teacherAssignmentId)
            ->whereBetween('date', [$startDate, $endDate])
            ->count();

        return $totalDays > 0;
    }
}
