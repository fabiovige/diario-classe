<?php

namespace App\Modules\Assessment\Domain\Strategies;

use App\Modules\Assessment\Domain\Entities\AssessmentConfig;
use App\Modules\Assessment\Domain\Entities\Grade;
use Illuminate\Database\Eloquent\Collection;

interface GradeCalculationStrategy
{
    /** @param Collection<int, Grade> $grades */
    public function calculateAverage(Collection $grades, AssessmentConfig $config): ?float;

    public function applyRecovery(float $originalAverage, float $recoveryGrade, AssessmentConfig $config): float;
}
