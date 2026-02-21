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
        '2025-02-17', '2025-02-19', '2025-02-21', '2025-02-24', '2025-02-26',
        '2025-02-28', '2025-03-03', '2025-03-05', '2025-03-07', '2025-03-10',
        '2025-03-12', '2025-03-14', '2025-03-17', '2025-03-19', '2025-03-21',
        '2025-03-24', '2025-03-26', '2025-03-28', '2025-03-31', '2025-04-02',
        '2025-04-23', '2025-04-25', '2025-04-28', '2025-04-30', '2025-05-02',
        '2025-05-05', '2025-05-07', '2025-05-09', '2025-05-12', '2025-05-14',
        '2025-05-16', '2025-05-19', '2025-05-21', '2025-05-23', '2025-05-26',
        '2025-05-28', '2025-05-30', '2025-06-02', '2025-06-04', '2025-06-06',
        '2025-08-04', '2025-08-06', '2025-08-08', '2025-08-11', '2025-08-13',
        '2025-08-15', '2025-08-18', '2025-08-20', '2025-08-22', '2025-08-25',
        '2025-08-27', '2025-08-29', '2025-09-01', '2025-09-03', '2025-09-05',
        '2025-09-08', '2025-09-10', '2025-09-12', '2025-09-15', '2025-09-17',
        '2025-10-01', '2025-10-03', '2025-10-06', '2025-10-08', '2025-10-10',
        '2025-10-13', '2025-10-15', '2025-10-17', '2025-10-20', '2025-10-22',
        '2025-10-24', '2025-10-27', '2025-10-29', '2025-10-31', '2025-11-03',
        '2025-11-05', '2025-11-07', '2025-11-10', '2025-11-12', '2025-11-14',
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
