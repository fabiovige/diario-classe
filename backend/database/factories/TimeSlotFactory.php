<?php

namespace Database\Factories;

use App\Modules\Curriculum\Domain\Entities\TimeSlot;
use App\Modules\SchoolStructure\Domain\Entities\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeSlotFactory extends Factory
{
    protected $model = TimeSlot::class;

    public function definition(): array
    {
        return [
            'shift_id' => Shift::factory(),
            'number' => 1,
            'start_time' => '07:00',
            'end_time' => '07:50',
            'type' => 'class',
        ];
    }

    public function break(): static
    {
        return $this->state(fn () => [
            'type' => 'break',
        ]);
    }
}
