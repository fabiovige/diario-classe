<?php

namespace App\Modules\Assessment\Application\UseCases;

use App\Modules\Assessment\Application\DTOs\CreateAssessmentConfigDTO;
use App\Modules\Assessment\Domain\Entities\AssessmentConfig;

final class CreateAssessmentConfigUseCase
{
    public function execute(CreateAssessmentConfigDTO $dto): AssessmentConfig
    {
        return AssessmentConfig::updateOrCreate(
            [
                'school_id' => $dto->schoolId,
                'academic_year_id' => $dto->academicYearId,
                'grade_level_id' => $dto->gradeLevelId,
            ],
            [
                'grade_type' => $dto->gradeType,
                'scale_min' => $dto->scaleMin,
                'scale_max' => $dto->scaleMax,
                'passing_grade' => $dto->passingGrade,
                'average_formula' => $dto->averageFormula,
                'rounding_precision' => $dto->roundingPrecision,
                'recovery_enabled' => $dto->recoveryEnabled,
                'recovery_replaces' => $dto->recoveryReplaces,
            ],
        );
    }
}
