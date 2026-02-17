<?php

namespace App\Modules\Attendance\Domain\Services;

use App\Modules\Attendance\Domain\Entities\AttendanceConfig;
use App\Modules\Attendance\Domain\Entities\AttendanceRecord;
use App\Modules\Attendance\Domain\Enums\AttendanceStatus;

final class AttendanceAlertChecker
{
    /** @return array<string, mixed> */
    public function check(int $studentId, int $classGroupId, int $schoolId, int $academicYearId): array
    {
        $config = AttendanceConfig::where('school_id', $schoolId)
            ->where('academic_year_id', $academicYearId)
            ->first();

        if ($config === null) {
            return ['alerts' => []];
        }

        $alerts = [];

        $consecutiveAbsences = $this->getConsecutiveAbsences($studentId, $classGroupId);
        if ($consecutiveAbsences >= $config->consecutive_absences_alert) {
            $alerts[] = [
                'type' => 'consecutive_absences',
                'threshold' => $config->consecutive_absences_alert,
                'current' => $consecutiveAbsences,
                'message' => "Aluno com {$consecutiveAbsences} faltas consecutivas (limite: {$config->consecutive_absences_alert}).",
            ];
        }

        $monthlyAbsences = $this->getMonthlyAbsences($studentId, $classGroupId);
        if ($monthlyAbsences >= $config->monthly_absences_alert) {
            $alerts[] = [
                'type' => 'monthly_absences',
                'threshold' => $config->monthly_absences_alert,
                'current' => $monthlyAbsences,
                'message' => "Aluno com {$monthlyAbsences} faltas no mês (limite: {$config->monthly_absences_alert}).",
            ];
        }

        return ['alerts' => $alerts];
    }

    private function getConsecutiveAbsences(int $studentId, int $classGroupId): int
    {
        $records = AttendanceRecord::where('student_id', $studentId)
            ->where('class_group_id', $classGroupId)
            ->orderByDesc('date')
            ->limit(30)
            ->get();

        $count = 0;
        foreach ($records as $record) {
            if ($record->status !== AttendanceStatus::Absent) {
                break;
            }
            $count++;
        }

        return $count;
    }

    private function getMonthlyAbsences(int $studentId, int $classGroupId): int
    {
        return AttendanceRecord::where('student_id', $studentId)
            ->where('class_group_id', $classGroupId)
            ->where('status', AttendanceStatus::Absent->value)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->count();
    }
}
