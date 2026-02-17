<?php

namespace Database\Factories;

use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\PeriodClosing\Domain\Entities\PeriodClosing;
use App\Modules\PeriodClosing\Domain\Enums\ClosingStatus;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PeriodClosing> */
class PeriodClosingFactory extends Factory
{
    protected $model = PeriodClosing::class;

    public function definition(): array
    {
        return [
            'class_group_id' => ClassGroup::factory(),
            'teacher_assignment_id' => TeacherAssignment::factory(),
            'assessment_period_id' => AssessmentPeriod::factory(),
            'status' => ClosingStatus::Pending->value,
            'all_grades_complete' => false,
            'all_attendance_complete' => false,
            'all_lesson_records_complete' => false,
        ];
    }
}
