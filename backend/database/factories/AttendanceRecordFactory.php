<?php

namespace Database\Factories;

use App\Modules\Attendance\Domain\Entities\AttendanceRecord;
use App\Modules\Attendance\Domain\Enums\AttendanceStatus;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\People\Domain\Entities\Student;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AttendanceRecord> */
class AttendanceRecordFactory extends Factory
{
    protected $model = AttendanceRecord::class;

    public function definition(): array
    {
        return [
            'class_group_id' => ClassGroup::factory(),
            'teacher_assignment_id' => TeacherAssignment::factory(),
            'student_id' => Student::factory(),
            'date' => fake()->date(),
            'status' => AttendanceStatus::Present->value,
            'recorded_by' => null,
        ];
    }
}
