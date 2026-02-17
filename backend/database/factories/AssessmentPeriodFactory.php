<?php

namespace Database\Factories;

use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\AcademicCalendar\Domain\Enums\AssessmentPeriodStatus;
use App\Modules\AcademicCalendar\Domain\Enums\AssessmentPeriodType;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AssessmentPeriod> */
class AssessmentPeriodFactory extends Factory
{
    protected $model = AssessmentPeriod::class;

    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'type' => AssessmentPeriodType::Bimestral->value,
            'number' => 1,
            'name' => '1º Bimestre',
            'start_date' => '2026-02-09',
            'end_date' => '2026-04-17',
            'status' => AssessmentPeriodStatus::Open->value,
        ];
    }
}
