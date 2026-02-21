<?php

namespace Database\Seeders;

use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Entities\School;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $schools = School::all();

        foreach ($schools as $school) {
            AcademicYear::updateOrCreate(
                ['school_id' => $school->id, 'year' => 2025],
                [
                    'status' => 'active',
                    'start_date' => '2025-02-10',
                    'end_date' => '2025-12-19',
                ],
            );
        }
    }
}
