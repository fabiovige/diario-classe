<?php

namespace App\Modules\Assessment\Application\DTOs;

final readonly class CreateAssessmentConfigDTO
{
    public function __construct(
        public int $schoolId,
        public int $academicYearId,
        public int $gradeLevelId,
        public string $gradeType,
        public ?float $scaleMin = null,
        public ?float $scaleMax = null,
        public ?float $passingGrade = null,
        public string $averageFormula = 'arithmetic',
        public int $roundingPrecision = 1,
        public bool $recoveryEnabled = true,
        public string $recoveryReplaces = 'higher',
    ) {}
}
