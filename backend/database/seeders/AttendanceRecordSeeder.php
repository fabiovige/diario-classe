<?php

namespace Database\Seeders;

use Database\Seeders\Traits\GeneratesSchoolDays;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceRecordSeeder extends Seeder
{
    use GeneratesSchoolDays;

    private const BATCH_SIZE = 5000;

    private const BIMESTRE_1_START = '2025-02-10';

    private const BIMESTRE_1_END = '2025-04-17';

    public function run(): void
    {
        DB::disableQueryLog();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('SET UNIQUE_CHECKS=0');

        $schoolDays = $this->generateSchoolDays(2025, self::BIMESTRE_1_START, self::BIMESTRE_1_END);
        $now = now()->toDateTimeString();

        $classGroups = DB::table('class_assignments')
            ->where('status', 'active')
            ->distinct()
            ->pluck('class_group_id')
            ->toArray();

        $teacherAssignments = DB::table('teacher_assignments')
            ->where('teacher_assignments.active', true)
            ->whereIn('teacher_assignments.class_group_id', $classGroups)
            ->join('teachers', 'teachers.id', '=', 'teacher_assignments.teacher_id')
            ->select('teacher_assignments.id', 'teacher_assignments.class_group_id', 'teachers.user_id')
            ->get()
            ->groupBy('class_group_id');

        $studentsByClass = DB::table('class_assignments')
            ->where('class_assignments.status', 'active')
            ->whereIn('class_assignments.class_group_id', $classGroups)
            ->join('enrollments', 'enrollments.id', '=', 'class_assignments.enrollment_id')
            ->select('class_assignments.class_group_id', 'enrollments.student_id')
            ->get()
            ->groupBy('class_group_id');

        $total = count($classGroups);
        $batch = [];

        foreach ($classGroups as $index => $classGroupId) {
            $tas = $teacherAssignments->get($classGroupId);
            $students = $studentsByClass->get($classGroupId);

            if (! $tas || ! $students) {
                continue;
            }

            $studentIds = $students->pluck('student_id')->toArray();
            $dayStatuses = $this->preGenerateStatuses($schoolDays, $studentIds);

            foreach ($tas as $ta) {
                foreach ($schoolDays as $date) {
                    foreach ($studentIds as $studentId) {
                        $batch[] = [
                            'class_group_id' => $classGroupId,
                            'teacher_assignment_id' => $ta->id,
                            'student_id' => $studentId,
                            'date' => $date,
                            'status' => $dayStatuses["$date|$studentId"],
                            'recorded_by' => $ta->user_id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        if (count($batch) >= self::BATCH_SIZE) {
                            DB::table('attendance_records')->insert($batch);
                            $batch = [];
                        }
                    }
                }
            }

            unset($dayStatuses);

            if (($index + 1) % 100 === 0) {
                $this->command->info("  Attendance: " . ($index + 1) . "/$total turmas...");
            }
        }

        if (! empty($batch)) {
            DB::table('attendance_records')->insert($batch);
        }

        DB::statement('SET UNIQUE_CHECKS=1');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info("  Attendance: $total/$total turmas finalizadas.");
    }

    /**
     * @param  list<string>  $schoolDays
     * @param  list<int>  $studentIds
     * @return array<string, string>
     */
    private function preGenerateStatuses(array $schoolDays, array $studentIds): array
    {
        $statuses = [];

        foreach ($schoolDays as $date) {
            foreach ($studentIds as $studentId) {
                $statuses["$date|$studentId"] = $this->resolveStatus();
            }
        }

        return $statuses;
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
