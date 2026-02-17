<?php

namespace App\Modules\Assessment\Domain\Strategies;

use App\Modules\Assessment\Domain\Enums\GradeType;

final class GradeCalculationFactory
{
    public function resolve(GradeType $type): GradeCalculationStrategy
    {
        return match ($type) {
            GradeType::Numeric => new NumericGradeCalculation(),
            GradeType::Conceptual => new ConceptualGradeCalculation(),
            GradeType::Descriptive => new DescriptiveGradeCalculation(),
        };
    }
}
