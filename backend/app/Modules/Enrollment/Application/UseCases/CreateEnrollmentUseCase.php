<?php

namespace App\Modules\Enrollment\Application\UseCases;

use App\Modules\Enrollment\Application\DTOs\CreateEnrollmentDTO;
use App\Modules\Enrollment\Domain\Entities\Enrollment;
use App\Modules\Enrollment\Domain\Entities\EnrollmentMovement;
use App\Modules\Enrollment\Domain\Enums\MovementType;
use App\Modules\Enrollment\Domain\Services\EnrollmentNumberGenerator;
use Illuminate\Support\Facades\DB;

final class CreateEnrollmentUseCase
{
    public function __construct(
        private readonly EnrollmentNumberGenerator $generator,
    ) {}

    public function execute(CreateEnrollmentDTO $dto): Enrollment
    {
        return DB::transaction(function () use ($dto) {
            $enrollmentNumber = $dto->enrollmentNumber;
            if ($enrollmentNumber === null || $enrollmentNumber === '') {
                $enrollmentNumber = $this->generator->generate($dto->schoolId, $dto->academicYearId);
            }

            $enrollment = Enrollment::create([
                'student_id' => $dto->studentId,
                'academic_year_id' => $dto->academicYearId,
                'school_id' => $dto->schoolId,
                'enrollment_number' => $enrollmentNumber,
                'enrollment_type' => $dto->enrollmentType,
                'enrollment_date' => $dto->enrollmentDate,
                'status' => 'active',
            ]);

            EnrollmentMovement::create([
                'enrollment_id' => $enrollment->id,
                'type' => MovementType::InitialEnrollment->value,
                'movement_date' => $dto->enrollmentDate,
                'reason' => 'Matrícula inicial',
                'created_by' => auth()->id(),
            ]);

            return $enrollment;
        });
    }
}
