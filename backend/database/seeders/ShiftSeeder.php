<?php

namespace Database\Seeders;

use App\Modules\SchoolStructure\Domain\Entities\School;
use App\Modules\SchoolStructure\Domain\Entities\Shift;
use App\Modules\SchoolStructure\Domain\Enums\ShiftName;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    private const SHIFTS = [
        ['name' => ShiftName::Morning, 'start_time' => '07:00', 'end_time' => '12:00'],
        ['name' => ShiftName::Afternoon, 'start_time' => '13:00', 'end_time' => '17:30'],
    ];

    private const INTEGRAL_SHIFT = ['name' => ShiftName::FullTime, 'start_time' => '07:00', 'end_time' => '17:30'];

    private const INTEGRAL_SCHOOL_LIMIT = 10;

    public function run(): void
    {
        $schools = School::orderBy('id')->get();

        foreach ($schools as $index => $school) {
            $shifts = $index < self::INTEGRAL_SCHOOL_LIMIT
                ? [...self::SHIFTS, self::INTEGRAL_SHIFT]
                : self::SHIFTS;

            foreach ($shifts as $shift) {
                Shift::updateOrCreate(
                    ['school_id' => $school->id, 'name' => $shift['name']],
                    ['start_time' => $shift['start_time'], 'end_time' => $shift['end_time']],
                );
            }
        }
    }
}
