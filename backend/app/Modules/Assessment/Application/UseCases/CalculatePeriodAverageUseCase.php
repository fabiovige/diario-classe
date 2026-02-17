<?php

namespace App\Modules\Assessment\Application\UseCases;

use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\Assessment\Domain\Entities\AssessmentConfig;
use App\Modules\Assessment\Domain\Entities\Grade;
use App\Modules\Assessment\Domain\Entities\PeriodAverage;
use App\Modules\Assessment\Domain\Strategies\GradeCalculationFactory;
use App\Modules\Attendance\Domain\Services\FrequencyCalculator;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;

final class CalculatePeriodAverageUseCase
{
    public function __construct(
        private GradeCalculationFactory $calculationFactory,
        private FrequencyCalculator $frequencyCalculator,
    ) {}

    public function execute(
        int $studentId,
        int $classGroupId,
        int $teacherAssignmentId,
        int $assessmentPeriodId,
    ): PeriodAverage {
        $classGroup = ClassGroup::with('academicYear')->findOrFail($classGroupId);
        $assignment = TeacherAssignment::findOrFail($teacherAssignmentId);
        $period = AssessmentPeriod::findOrFail($assessmentPeriodId);

        $config = AssessmentConfig::where('school_id', $classGroup->academicYear->school_id)
            ->where('academic_year_id', $classGroup->academic_year_id)
            ->where('grade_level_id', $classGroup->grade_level_id)
            ->first();

        $grades = Grade::where('student_id', $studentId)
            ->where('class_group_id', $classGroupId)
            ->where('teacher_assignment_id', $teacherAssignmentId)
            ->where('assessment_period_id', $assessmentPeriodId)
            ->with('assessmentInstrument')
            ->get();

        $numericAverage = null;
        $conceptualAverage = null;

        if ($config !== null) {
            $strategy = $this->calculationFactory->resolve($config->grade_type);
            $average = $strategy->calculateAverage($grades, $config);

            $recoveryGrade = $grades->where('is_recovery', true)->first();
            if ($average !== null && $recoveryGrade !== null && $config->recovery_enabled) {
                $recoveryValue = (float) ($recoveryGrade->numeric_value ?? 0);
                $average = $strategy->applyRecovery($average, $recoveryValue, $config);
            }

            $numericAverage = $average;
        }

        $frequency = $this->frequencyCalculator->calculate(
            studentId: $studentId,
            classGroupId: $classGroupId,
            teacherAssignmentId: $teacherAssignmentId,
            startDate: $period->start_date->format('Y-m-d'),
            endDate: $period->end_date->format('Y-m-d'),
        );

        return PeriodAverage::updateOrCreate(
            [
                'student_id' => $studentId,
                'class_group_id' => $classGroupId,
                'teacher_assignment_id' => $teacherAssignmentId,
                'assessment_period_id' => $assessmentPeriodId,
            ],
            [
                'numeric_average' => $numericAverage,
                'conceptual_average' => $conceptualAverage,
                'total_absences' => $frequency['absent'],
                'frequency_percentage' => $frequency['frequency_percentage'],
                'calculated_at' => now(),
            ],
        );
    }
}
