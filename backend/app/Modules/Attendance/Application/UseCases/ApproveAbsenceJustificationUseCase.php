<?php

namespace App\Modules\Attendance\Application\UseCases;

use App\Modules\Attendance\Domain\Entities\AbsenceJustification;
use App\Modules\Attendance\Domain\Entities\AttendanceRecord;
use App\Modules\Attendance\Domain\Enums\AttendanceStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ApproveAbsenceJustificationUseCase
{
    public function execute(int $justificationId): AbsenceJustification
    {
        return DB::transaction(function () use ($justificationId) {
            $justification = AbsenceJustification::findOrFail($justificationId);

            if ($justification->approved) {
                throw ValidationException::withMessages([
                    'id' => 'Esta justificativa já foi aprovada.',
                ]);
            }

            $justification->update([
                'approved' => true,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            AttendanceRecord::where('student_id', $justification->student_id)
                ->where('status', AttendanceStatus::Absent->value)
                ->whereBetween('date', [$justification->start_date, $justification->end_date])
                ->update(['status' => AttendanceStatus::JustifiedAbsence->value]);

            return $justification->refresh();
        });
    }
}
