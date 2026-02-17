<?php

namespace Database\Factories;

use App\Modules\Attendance\Domain\Entities\AttendanceConfig;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Entities\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AttendanceConfig> */
class AttendanceConfigFactory extends Factory
{
    protected $model = AttendanceConfig::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'consecutive_absences_alert' => 5,
            'monthly_absences_alert' => 10,
            'period_absence_percentage_alert' => 25.00,
            'annual_minimum_frequency' => 75.00,
        ];
    }
}
