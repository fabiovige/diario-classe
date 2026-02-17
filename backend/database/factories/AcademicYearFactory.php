<?php

namespace Database\Factories;

use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Entities\School;
use App\Modules\SchoolStructure\Domain\Enums\AcademicYearStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AcademicYear> */
class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'year' => 2026,
            'status' => AcademicYearStatus::Active->value,
            'start_date' => '2026-02-09',
            'end_date' => '2026-12-18',
        ];
    }
}
