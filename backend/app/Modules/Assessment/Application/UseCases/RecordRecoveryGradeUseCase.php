<?php

namespace App\Modules\Assessment\Application\UseCases;

use App\Modules\Assessment\Domain\Entities\Grade;

final class RecordRecoveryGradeUseCase
{
    public function execute(
        int $studentId,
        int $classGroupId,
        int $teacherAssignmentId,
        int $assessmentPeriodId,
        int $assessmentInstrumentId,
        ?float $numericValue,
        ?string $conceptualValue,
        string $recoveryType,
    ): Grade {
        return Grade::updateOrCreate(
            [
                'student_id' => $studentId,
                'class_group_id' => $classGroupId,
                'teacher_assignment_id' => $teacherAssignmentId,
                'assessment_period_id' => $assessmentPeriodId,
                'assessment_instrument_id' => $assessmentInstrumentId,
                'is_recovery' => true,
            ],
            [
                'numeric_value' => $numericValue,
                'conceptual_value' => $conceptualValue,
                'recovery_type' => $recoveryType,
                'recorded_by' => auth()->id(),
            ],
        );
    }
}
