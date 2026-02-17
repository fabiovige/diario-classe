<?php

namespace App\Modules\Assessment\Domain\Strategies;

use App\Modules\Assessment\Domain\Entities\AssessmentConfig;
use App\Modules\Assessment\Domain\Entities\Grade;
use Illuminate\Database\Eloquent\Collection;

final class DescriptiveGradeCalculation implements GradeCalculationStrategy
{
    /** @param Collection<int, Grade> $grades */
    public function calculateAverage(Collection $grades, AssessmentConfig $config): ?float
    {
        return null;
    }

    public function applyRecovery(float $originalAverage, float $recoveryGrade, AssessmentConfig $config): float
    {
        return $originalAverage;
    }
}
