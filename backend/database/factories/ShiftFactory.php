<?php

namespace Database\Factories;

use App\Modules\SchoolStructure\Domain\Entities\School;
use App\Modules\SchoolStructure\Domain\Entities\Shift;
use App\Modules\SchoolStructure\Domain\Enums\ShiftName;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Shift> */
class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'name' => fake()->randomElement(ShiftName::cases()),
            'start_time' => '07:00',
            'end_time' => '12:00',
        ];
    }
}
