<?php

namespace Database\Factories;

use App\Modules\Attendance\Domain\Entities\AbsenceJustification;
use App\Modules\People\Domain\Entities\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AbsenceJustification> */
class AbsenceJustificationFactory extends Factory
{
    protected $model = AbsenceJustification::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-03',
            'reason' => fake()->sentence(),
            'document_path' => null,
            'approved' => false,
            'approved_by' => null,
            'approved_at' => null,
            'created_by' => null,
        ];
    }
}
