<?php

namespace Database\Factories;

use App\Modules\Assessment\Domain\Entities\AssessmentConfig;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Entities\GradeLevel;
use App\Modules\SchoolStructure\Domain\Entities\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AssessmentConfig> */
class AssessmentConfigFactory extends Factory
{
    protected $model = AssessmentConfig::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'grade_level_id' => GradeLevel::factory(),
            'grade_type' => 'numeric',
            'scale_min' => 0,
            'scale_max' => 10,
            'passing_grade' => 6,
            'average_formula' => 'arithmetic',
            'rounding_precision' => 1,
            'recovery_enabled' => true,
            'recovery_replaces' => 'higher',
        ];
    }
}
