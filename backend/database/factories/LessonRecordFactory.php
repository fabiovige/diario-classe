<?php

namespace Database\Factories;

use App\Modules\ClassRecord\Domain\Entities\LessonRecord;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LessonRecord> */
class LessonRecordFactory extends Factory
{
    protected $model = LessonRecord::class;

    public function definition(): array
    {
        return [
            'class_group_id' => ClassGroup::factory(),
            'teacher_assignment_id' => TeacherAssignment::factory(),
            'date' => fake()->date(),
            'content' => fake()->paragraph(),
            'methodology' => fake()->sentence(),
            'observations' => null,
            'class_count' => 1,
            'recorded_by' => null,
        ];
    }
}
