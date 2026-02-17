<?php

namespace App\Modules\Attendance\Application\UseCases;

use App\Modules\Attendance\Application\DTOs\CreateAbsenceJustificationDTO;
use App\Modules\Attendance\Domain\Entities\AbsenceJustification;
use App\Modules\Attendance\Domain\Entities\AttendanceRecord;
use App\Modules\Attendance\Domain\Enums\AttendanceStatus;
use Illuminate\Support\Facades\DB;

final class JustifyAbsenceUseCase
{
    public function execute(CreateAbsenceJustificationDTO $dto): AbsenceJustification
    {
        return DB::transaction(function () use ($dto) {
            $justification = AbsenceJustification::create([
                'student_id' => $dto->studentId,
                'start_date' => $dto->startDate,
                'end_date' => $dto->endDate,
                'reason' => $dto->reason,
                'document_path' => $dto->documentPath,
                'approved' => false,
                'created_by' => auth()->id(),
            ]);

            return $justification;
        });
    }
}
