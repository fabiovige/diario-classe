<?php

namespace App\Modules\PeriodClosing\Application\UseCases;

use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\Assessment\Domain\Entities\AssessmentConfig;
use App\Modules\PeriodClosing\Domain\Entities\PeriodClosing;
use App\Modules\PeriodClosing\Domain\Specifications\AttendanceCompleteSpecification;
use App\Modules\PeriodClosing\Domain\Specifications\GradesCompleteSpecification;
use App\Modules\PeriodClosing\Domain\Specifications\LessonRecordsCompleteSpecification;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;

final class RunCompletenessCheckUseCase
{
    public function __construct(
        private GradesCompleteSpecification $gradesSpec,
        private AttendanceCompleteSpecification $attendanceSpec,
        private LessonRecordsCompleteSpecification $lessonSpec,
    ) {}

    public function execute(int $periodClosingId): PeriodClosing
    {
        $closing = PeriodClosing::findOrFail($periodClosingId);
        $period = AssessmentPeriod::findOrFail($closing->assessment_period_id);
        $classGroup = ClassGroup::with('academicYear')->findOrFail($closing->class_group_id);

        $config = AssessmentConfig::where('school_id', $classGroup->academicYear->school_id)
            ->where('academic_year_id', $classGroup->academic_year_id)
            ->where('grade_level_id', $classGroup->grade_level_id)
            ->first();

        $gradesComplete = $config !== null
            ? $this->gradesSpec->isSatisfiedBy(
                $closing->class_group_id,
                $closing->teacher_assignment_id,
                $closing->assessment_period_id,
                $config->id,
            )
            : true;

        $attendanceComplete = $this->attendanceSpec->isSatisfiedBy(
            $closing->class_group_id,
            $closing->teacher_assignment_id,
            $period->start_date->format('Y-m-d'),
            $period->end_date->format('Y-m-d'),
        );

        $lessonComplete = $this->lessonSpec->isSatisfiedBy(
            $closing->class_group_id,
            $closing->teacher_assignment_id,
            $period->start_date->format('Y-m-d'),
            $period->end_date->format('Y-m-d'),
        );

        $closing->update([
            'all_grades_complete' => $gradesComplete,
            'all_attendance_complete' => $attendanceComplete,
            'all_lesson_records_complete' => $lessonComplete,
        ]);

        return $closing->refresh();
    }
}
