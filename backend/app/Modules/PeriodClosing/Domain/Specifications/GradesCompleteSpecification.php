<?php

namespace App\Modules\PeriodClosing\Domain\Specifications;

use App\Modules\Assessment\Domain\Entities\AssessmentInstrument;
use App\Modules\Assessment\Domain\Entities\Grade;
use App\Modules\Enrollment\Domain\Entities\ClassAssignment;

final class GradesCompleteSpecification
{
    public function isSatisfiedBy(int $classGroupId, int $teacherAssignmentId, int $assessmentPeriodId, int $assessmentConfigId): bool
    {
        $instruments = AssessmentInstrument::where('assessment_config_id', $assessmentConfigId)->pluck('id');

        if ($instruments->isEmpty()) {
            return true;
        }

        $studentIds = ClassAssignment::where('class_group_id', $classGroupId)
            ->where('status', 'active')
            ->pluck('enrollment_id');

        if ($studentIds->isEmpty()) {
            return true;
        }

        $enrollmentStudentIds = \App\Modules\Enrollment\Domain\Entities\Enrollment::whereIn('id', $studentIds)
            ->pluck('student_id');

        $expectedCount = $enrollmentStudentIds->count() * $instruments->count();

        $actualCount = Grade::where('class_group_id', $classGroupId)
            ->where('teacher_assignment_id', $teacherAssignmentId)
            ->where('assessment_period_id', $assessmentPeriodId)
            ->whereIn('assessment_instrument_id', $instruments)
            ->whereIn('student_id', $enrollmentStudentIds)
            ->where('is_recovery', false)
            ->count();

        return $actualCount >= $expectedCount;
    }
}
