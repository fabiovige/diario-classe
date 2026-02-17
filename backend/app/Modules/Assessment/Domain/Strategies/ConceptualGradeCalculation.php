<?php

namespace App\Modules\Assessment\Domain\Strategies;

use App\Modules\Assessment\Domain\Entities\AssessmentConfig;
use App\Modules\Assessment\Domain\Entities\ConceptualScale;
use App\Modules\Assessment\Domain\Entities\Grade;
use App\Modules\Assessment\Domain\Enums\RecoveryReplaces;
use Illuminate\Database\Eloquent\Collection;

final class ConceptualGradeCalculation implements GradeCalculationStrategy
{
    /** @param Collection<int, Grade> $grades */
    public function calculateAverage(Collection $grades, AssessmentConfig $config): ?float
    {
        $regularGrades = $grades->where('is_recovery', false);

        if ($regularGrades->isEmpty()) {
            return null;
        }

        $scales = ConceptualScale::where('assessment_config_id', $config->id)
            ->get()
            ->keyBy('code');

        $sum = 0;
        $count = 0;

        foreach ($regularGrades as $grade) {
            $scale = $scales->get($grade->conceptual_value);
            if ($scale === null) {
                continue;
            }
            $sum += $scale->numeric_equivalent;
            $count++;
        }

        if ($count === 0) {
            return null;
        }

        return round($sum / $count, $config->rounding_precision);
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
}
