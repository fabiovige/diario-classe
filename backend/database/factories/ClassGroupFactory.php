<?php

namespace Database\Factories;

use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\SchoolStructure\Domain\Entities\GradeLevel;
use App\Modules\SchoolStructure\Domain\Entities\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ClassGroup> */
class ClassGroupFactory extends Factory
{
    protected $model = ClassGroup::class;

    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'grade_level_id' => GradeLevel::factory(),
            'shift_id' => Shift::factory(),
            'name' => fake()->unique()->randomElement(['A', 'B', 'C', 'D', 'E']),
            'max_students' => 30,
        ];
    }
}
