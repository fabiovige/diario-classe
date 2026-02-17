<?php

namespace Database\Seeders;

use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use Illuminate\Database\Seeder;

class AssessmentPeriodSeeder extends Seeder
{
    private const BIMESTRES = [
        1 => ['name' => '1º Bimestre', 'start' => '-02-09', 'end' => '-04-17'],
        2 => ['name' => '2º Bimestre', 'start' => '-04-21', 'end' => '-06-30'],
        3 => ['name' => '3º Bimestre', 'start' => '-08-04', 'end' => '-09-30'],
        4 => ['name' => '4º Bimestre', 'start' => '-10-01', 'end' => '-12-18'],
    ];

    public function run(): void
    {
        $academicYears = AcademicYear::all();

        foreach ($academicYears as $academicYear) {
            foreach (self::BIMESTRES as $number => $config) {
                AssessmentPeriod::updateOrCreate(
                    [
                        'academic_year_id' => $academicYear->id,
                        'type' => 'bimestral',
                        'number' => $number,
                    ],
                    [
                        'name' => $config['name'],
                        'start_date' => $academicYear->year.$config['start'],
                        'end_date' => $academicYear->year.$config['end'],
                        'status' => 'open',
                    ],
                );
            }
        }
    }
}
