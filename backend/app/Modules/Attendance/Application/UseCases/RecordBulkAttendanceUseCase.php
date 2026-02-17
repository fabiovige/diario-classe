<?php

namespace App\Modules\Attendance\Application\UseCases;

use App\Modules\Attendance\Application\DTOs\RecordBulkAttendanceDTO;
use App\Modules\Attendance\Domain\Entities\AttendanceRecord;
use Illuminate\Support\Facades\DB;

final class RecordBulkAttendanceUseCase
{
    /** @return \Illuminate\Database\Eloquent\Collection<int, AttendanceRecord> */
    public function execute(RecordBulkAttendanceDTO $dto): \Illuminate\Database\Eloquent\Collection
    {
        return DB::transaction(function () use ($dto) {
            $rows = [];
            $now = now();

            foreach ($dto->records as $record) {
                $rows[] = [
                    'class_group_id' => $dto->classGroupId,
                    'teacher_assignment_id' => $dto->teacherAssignmentId,
                    'student_id' => $record['student_id'],
                    'date' => $dto->date,
                    'status' => $record['status'],
                    'recorded_by' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            AttendanceRecord::upsert(
                $rows,
                ['class_group_id', 'teacher_assignment_id', 'student_id', 'date'],
                ['status', 'recorded_by', 'updated_at'],
            );

            return AttendanceRecord::where('class_group_id', $dto->classGroupId)
                ->where('teacher_assignment_id', $dto->teacherAssignmentId)
                ->where('date', $dto->date)
                ->with('student')
                ->get();
        });
    }
}
