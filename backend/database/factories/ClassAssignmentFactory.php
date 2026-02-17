<?php

namespace Database\Factories;

use App\Modules\Enrollment\Domain\Entities\ClassAssignment;
use App\Modules\Enrollment\Domain\Entities\Enrollment;
use App\Modules\Enrollment\Domain\Enums\ClassAssignmentStatus;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ClassAssignment> */
class ClassAssignmentFactory extends Factory
{
    protected $model = ClassAssignment::class;

    public function definition(): array
    {
        return [
            'enrollment_id' => Enrollment::factory(),
            'class_group_id' => ClassGroup::factory(),
            'status' => ClassAssignmentStatus::Active->value,
            'start_date' => '2026-02-09',
        ];
    }
}
