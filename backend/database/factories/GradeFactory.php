<?php

namespace Database\Factories;

use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\Assessment\Domain\Entities\AssessmentInstrument;
use App\Modules\Assessment\Domain\Entities\Grade;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\People\Domain\Entities\Student;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Grade> */
class GradeFactory extends Factory
{
    protected $model = Grade::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'class_group_id' => ClassGroup::factory(),
            'teacher_assignment_id' => TeacherAssignment::factory(),
            'assessment_period_id' => AssessmentPeriod::factory(),
            'assessment_instrument_id' => AssessmentInstrument::factory(),
            'numeric_value' => fake()->randomFloat(1, 0, 10),
            'conceptual_value' => null,
            'observations' => null,
            'is_recovery' => false,
            'recovery_type' => null,
            'recorded_by' => null,
        ];
    }
}
