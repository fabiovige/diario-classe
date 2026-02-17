<?php

namespace App\Modules\Enrollment\Application\UseCases;

use App\Modules\Enrollment\Application\DTOs\AssignToClassDTO;
use App\Modules\Enrollment\Domain\Entities\ClassAssignment;
use App\Modules\Enrollment\Domain\Entities\Enrollment;
use App\Modules\Enrollment\Domain\Enums\ClassAssignmentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AssignToClassUseCase
{
    public function execute(AssignToClassDTO $dto): ClassAssignment
    {
        $enrollment = Enrollment::findOrFail($dto->enrollmentId);

        if (! $enrollment->isActive()) {
            throw ValidationException::withMessages([
                'enrollment_id' => ['Matrícula não está ativa.'],
            ]);
        }

        return DB::transaction(function () use ($dto, $enrollment) {
            $enrollment->classAssignments()
                ->where('status', ClassAssignmentStatus::Active->value)
                ->update([
                    'status' => ClassAssignmentStatus::Transferred->value,
                    'end_date' => $dto->startDate,
                ]);

            return ClassAssignment::create([
                'enrollment_id' => $dto->enrollmentId,
                'class_group_id' => $dto->classGroupId,
                'status' => ClassAssignmentStatus::Active->value,
                'start_date' => $dto->startDate,
            ]);
        });
    }
}
