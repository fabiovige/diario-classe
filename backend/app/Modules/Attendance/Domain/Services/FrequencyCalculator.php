<?php

namespace App\Modules\Attendance\Domain\Services;

use App\Modules\Attendance\Domain\Entities\AttendanceRecord;
use App\Modules\Attendance\Domain\Enums\AttendanceStatus;

final class FrequencyCalculator
{
    /** @return array{total_classes: int, present: int, absent: int, justified: int, excused: int, frequency_percentage: float} */
    public function calculate(int $studentId, int $classGroupId, ?int $teacherAssignmentId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = AttendanceRecord::where('student_id', $studentId)
            ->where('class_group_id', $classGroupId);

        if ($teacherAssignmentId !== null) {
            $query->where('teacher_assignment_id', $teacherAssignmentId);
        }

        if ($startDate !== null) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate !== null) {
            $query->where('date', '<=', $endDate);
        }

        $records = $query->get();
        $total = $records->count();

        if ($total === 0) {
            return [
                'total_classes' => 0,
                'present' => 0,
                'absent' => 0,
                'justified' => 0,
                'excused' => 0,
                'frequency_percentage' => 100.00,
            ];
        }

        $present = $records->where('status', AttendanceStatus::Present)->count();
        $absent = $records->where('status', AttendanceStatus::Absent)->count();
        $justified = $records->where('status', AttendanceStatus::JustifiedAbsence)->count();
        $excused = $records->where('status', AttendanceStatus::Excused)->count();

        $attended = $present + $justified + $excused;
        $percentage = round(($attended / $total) * 100, 2);

        return [
            'total_classes' => $total,
            'present' => $present,
            'absent' => $absent,
            'justified' => $justified,
            'excused' => $excused,
            'frequency_percentage' => $percentage,
        ];
    }
}
