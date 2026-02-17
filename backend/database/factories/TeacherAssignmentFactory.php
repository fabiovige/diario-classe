<?php

namespace Database\Factories;

use App\Modules\Curriculum\Domain\Entities\CurricularComponent;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\People\Domain\Entities\Teacher;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TeacherAssignment> */
class TeacherAssignmentFactory extends Factory
{
    protected $model = TeacherAssignment::class;

    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'class_group_id' => ClassGroup::factory(),
            'curricular_component_id' => CurricularComponent::factory(),
            'experience_field_id' => null,
            'start_date' => '2026-02-09',
            'end_date' => null,
            'active' => true,
        ];
    }
}
