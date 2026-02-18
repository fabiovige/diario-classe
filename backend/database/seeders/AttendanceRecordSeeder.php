<?php

namespace Database\Seeders;

use App\Modules\Attendance\Domain\Entities\AttendanceRecord;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\Enrollment\Domain\Entities\ClassAssignment;
use App\Modules\SchoolStructure\Domain\Entities\GradeLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceRecordSeeder extends Seeder
{
    private const SCHOOL_DAYS = [
        '2026-02-16', '2026-02-23', '2026-03-02', '2026-03-09', '2026-03-16',
        '2026-03-23', '2026-03-30', '2026-04-06', '2026-04-13', '2026-04-16',
        '2026-04-27', '2026-05-04', '2026-05-11', '2026-05-18', '2026-05-25',
        '2026-06-01', '2026-06-08', '2026-06-15', '2026-06-22', '2026-06-29',
    ];

    private const BATCH_SIZE = 1000;

    public function run(): void
    {
        $classGroupIds = ClassAssignment::where('status', 'active')
            ->distinct()
            ->pluck('class_group_id');

        $batch = [];

        foreach ($classGroupIds as $classGroupId) {
            $teacherAssignment = TeacherAssignment::where('class_group_id', $classGroupId)
                ->where('active', true)
                ->with('teacher')
                ->first();

            if (! $teacherAssignment) {
                continue;
            }

            $recordedBy = $teacherAssignment->teacher?->user_id;

            $studentIds = ClassAssignment::where('class_group_id', $classGroupId)
                ->where('status', 'active')
                ->with('enrollment')
                ->get()
                ->map(fn ($assignment) => $assignment->enrollment?->student_id)
                ->filter()
                ->values();

            foreach (self::SCHOOL_DAYS as $date) {
                foreach ($studentIds as $studentId) {
                    $batch[] = [
                        'class_group_id' => $classGroupId,
                        'teacher_assignment_id' => $teacherAssignment->id,
                        'student_id' => $studentId,
                        'date' => $date,
                        'status' => $this->resolveStatus(),
                        'recorded_by' => $recordedBy,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (count($batch) >= self::BATCH_SIZE) {
                        DB::table('attendance_records')->insert($batch);
                        $batch = [];
                    }
                }
            }
        }

        if (! empty($batch)) {
            DB::table('attendance_records')->insert($batch);
        }
    }

    private function resolveStatus(): string
    {
        $roll = rand(1, 100);

        if ($roll <= 85) {
            return 'present';
        }

        if ($roll <= 95) {
            return 'absent';
        }

        return 'justified_absence';
    }
}
