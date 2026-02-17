<?php

namespace App\Modules\Assessment\Domain\Strategies;

use App\Modules\Assessment\Domain\Entities\AssessmentConfig;
use App\Modules\Assessment\Domain\Entities\Grade;
use App\Modules\Assessment\Domain\Enums\AverageFormula;
use App\Modules\Assessment\Domain\Enums\RecoveryReplaces;
use Illuminate\Database\Eloquent\Collection;

final class NumericGradeCalculation implements GradeCalculationStrategy
{
    /** @param Collection<int, Grade> $grades */
    public function calculateAverage(Collection $grades, AssessmentConfig $config): ?float
    {
        $regularGrades = $grades->where('is_recovery', false);

        if ($regularGrades->isEmpty()) {
            return null;
        }

        if ($config->average_formula === AverageFormula::Weighted) {
            return $this->weightedAverage($regularGrades, $config);
        }

        return $this->arithmeticAverage($regularGrades, $config);
    }

    public function applyRecovery(float $originalAverage, float $recoveryGrade, AssessmentConfig $config): float
    {
        $result = match ($config->recovery_replaces) {
            RecoveryReplaces::Higher => max($originalAverage, $recoveryGrade),
            RecoveryReplaces::Average => ($originalAverage + $recoveryGrade) / 2,
            RecoveryReplaces::Last => $recoveryGrade,
        };

        return round($result, $config->rounding_precision);
    }

    /** @param Collection<int, Grade> $grades */
    private function arithmeticAverage(Collection $grades, AssessmentConfig $config): float
    {
        $sum = $grades->sum('numeric_value');
        $count = $grades->count();

        return round($sum / $count, $config->rounding_precision);
    }

    /** @param Collection<int, Grade> $grades */
    private function weightedAverage(Collection $grades, AssessmentConfig $config): float
    {
        $weightedSum = 0;
        $totalWeight = 0;

        foreach ($grades as $grade) {
            $weight = $grade->assessmentInstrument->weight ?? 1;
            $weightedSum += $grade->numeric_value * $weight;
            $totalWeight += $weight;
        }

        if ($totalWeight === 0) {
            return 0;
        }

        return round($weightedSum / $totalWeight, $config->rounding_precision);
    }
}
