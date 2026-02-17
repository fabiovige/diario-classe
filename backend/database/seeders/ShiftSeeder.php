<?php

namespace Database\Seeders;

use App\Modules\SchoolStructure\Domain\Entities\School;
use App\Modules\SchoolStructure\Domain\Entities\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    private const FIRST_TEN_LIMIT = 10;

    public function run(): void
    {
        $schools = School::orderBy('id')->get();

        foreach ($schools as $index => $school) {
            Shift::updateOrCreate(
                ['school_id' => $school->id, 'name' => 'Manhã'],
                ['start_time' => '07:00', 'end_time' => '12:00'],
            );

            Shift::updateOrCreate(
                ['school_id' => $school->id, 'name' => 'Tarde'],
                ['start_time' => '13:00', 'end_time' => '17:30'],
            );

            if ($index >= self::FIRST_TEN_LIMIT) {
                continue;
            }

            Shift::updateOrCreate(
                ['school_id' => $school->id, 'name' => 'Integral'],
                ['start_time' => '07:00', 'end_time' => '17:30'],
            );
        }
    }
}
