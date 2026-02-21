<?php

namespace Database\Factories;

use App\Modules\Enrollment\Domain\Entities\Enrollment;
use App\Modules\Enrollment\Domain\Enums\EnrollmentStatus;
use App\Modules\People\Domain\Entities\Student;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Entities\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Enrollment> */
class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    private static int $sequence = 0;

    public function definition(): array
    {
        self::$sequence++;

        return [
            'student_id' => Student::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'school_id' => School::factory(),
            'enrollment_number' => sprintf('2026-001-%05d', self::$sequence),
            'status' => EnrollmentStatus::Active->value,
            'enrollment_date' => '2026-02-09',
        ];
    }
}
