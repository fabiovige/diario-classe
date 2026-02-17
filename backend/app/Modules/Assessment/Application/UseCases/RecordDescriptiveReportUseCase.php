<?php

namespace App\Modules\Assessment\Application\UseCases;

use App\Modules\Assessment\Domain\Entities\DescriptiveReport;

final class RecordDescriptiveReportUseCase
{
    public function execute(
        int $studentId,
        int $classGroupId,
        int $experienceFieldId,
        int $assessmentPeriodId,
        string $content,
    ): DescriptiveReport {
        return DescriptiveReport::updateOrCreate(
            [
                'student_id' => $studentId,
                'class_group_id' => $classGroupId,
                'experience_field_id' => $experienceFieldId,
                'assessment_period_id' => $assessmentPeriodId,
            ],
            [
                'content' => $content,
                'recorded_by' => auth()->id(),
            ],
        );
    }
}
