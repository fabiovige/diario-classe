<?php

namespace Database\Seeders;

use App\Modules\Attendance\Domain\Entities\AttendanceConfig;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use Illuminate\Database\Seeder;

class AttendanceConfigSeeder extends Seeder
{
    public function run(): void
    {
        $academicYears = AcademicYear::all();

        foreach ($academicYears as $academicYear) {
            AttendanceConfig::updateOrCreate(
                [
                    'school_id' => $academicYear->school_id,
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'consecutive_absences_alert' => 5,
                    'monthly_absences_alert' => 10,
                    'period_absence_percentage_alert' => 25.00,
                    'annual_minimum_frequency' => 75.00,
                ],
            );
        }
    }
}
