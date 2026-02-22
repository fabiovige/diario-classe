<?php

namespace Database\Factories;

use App\Modules\Curriculum\Domain\Entities\ClassSchedule;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\Curriculum\Domain\Entities\TimeSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassScheduleFactory extends Factory
{
    protected $model = ClassSchedule::class;

    public function definition(): array
    {
        return [
            'teacher_assignment_id' => TeacherAssignment::factory(),
            'time_slot_id' => TimeSlot::factory(),
            'day_of_week' => $this->faker->numberBetween(1, 5),
        ];
    }
}
