<?php

namespace App\Modules\Enrollment\Application\UseCases;

use App\Modules\Enrollment\Domain\Entities\Enrollment;
use App\Modules\Enrollment\Domain\Entities\EnrollmentMovement;
use App\Modules\Enrollment\Domain\Enums\ClassAssignmentStatus;
use App\Modules\Enrollment\Domain\Enums\EnrollmentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class TransferEnrollmentUseCase
{
    public function execute(
        int $enrollmentId,
        string $type,
        string $date,
        ?string $reason = null,
        ?int $originSchoolId = null,
        ?int $destinationSchoolId = null,
    ): Enrollment {
        $enrollment = Enrollment::findOrFail($enrollmentId);

        if (! $enrollment->isActive()) {
            throw ValidationException::withMessages([
                'enrollment_id' => ['Matrícula não está ativa.'],
            ]);
        }

        $resolvedOriginSchoolId = $originSchoolId ?? $enrollment->school_id;

        return DB::transaction(function () use ($enrollment, $type, $date, $reason, $resolvedOriginSchoolId, $destinationSchoolId) {
            $enrollment->classAssignments()
                ->where('status', ClassAssignmentStatus::Active->value)
                ->update([
                    'status' => ClassAssignmentStatus::Transferred->value,
                    'end_date' => $date,
                ]);

            $enrollment->update([
                'status' => EnrollmentStatus::Transferred->value,
                'exit_date' => $date,
            ]);

            EnrollmentMovement::create([
                'enrollment_id' => $enrollment->id,
                'type' => $type,
                'movement_date' => $date,
                'reason' => $reason,
                'origin_school_id' => $resolvedOriginSchoolId,
                'destination_school_id' => $destinationSchoolId,
                'created_by' => auth()->id(),
            ]);

            return $enrollment->refresh();
        });
    }
}
