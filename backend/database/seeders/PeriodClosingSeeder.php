<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeriodClosingSeeder extends Seeder
{
    private const BATCH_SIZE = 200;

    public function run(): void
    {
        DB::disableQueryLog();

        $adminId = DB::table('users')
            ->where('email', 'admin@jandira.sp.gov.br')
            ->value('id');

        $classGroupIds = DB::table('class_assignments')
            ->where('status', 'active')
            ->distinct()
            ->pluck('class_group_id')
            ->toArray();

        $classGroups = DB::table('class_groups')
            ->whereIn('id', $classGroupIds)
            ->pluck('academic_year_id', 'id');

        $periods = DB::table('assessment_periods')
            ->whereIn('number', [1, 2, 3, 4])
            ->get()
            ->groupBy('academic_year_id');

        $teacherAssignments = DB::table('teacher_assignments')
            ->where('teacher_assignments.active', true)
            ->whereIn('teacher_assignments.class_group_id', $classGroupIds)
            ->select('teacher_assignments.id', 'teacher_assignments.class_group_id')
            ->get()
            ->groupBy('class_group_id');

        $now = now()->toDateTimeString();
        $batch = [];
        $total = count($classGroupIds);

        foreach ($classGroupIds as $index => $classGroupId) {
            $academicYearId = $classGroups->get($classGroupId);
            $ayPeriods = $periods->get($academicYearId);
            $tas = $teacherAssignments->get($classGroupId);

            if (! $ayPeriods || ! $tas) {
                continue;
            }

            foreach ($tas as $ta) {
                foreach ($ayPeriods as $period) {
                    $row = [
                        'class_group_id' => $classGroupId,
                        'teacher_assignment_id' => $ta->id,
                        'assessment_period_id' => $period->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    if ($period->number === 1) {
                        $row += [
                            'status' => 'approved',
                            'all_grades_complete' => true,
                            'all_attendance_complete' => true,
                            'all_lesson_records_complete' => true,
                            'submitted_by' => $adminId,
                            'submitted_at' => '2025-04-18',
                            'validated_by' => $adminId,
                            'validated_at' => '2025-04-19',
                            'approved_by' => $adminId,
                            'approved_at' => '2025-04-20',
                        ];
                    }

                    if ($period->number > 1) {
                        $row += [
                            'status' => 'pending',
                            'all_grades_complete' => false,
                            'all_attendance_complete' => false,
                            'all_lesson_records_complete' => false,
                            'submitted_by' => null,
                            'submitted_at' => null,
                            'validated_by' => null,
                            'validated_at' => null,
                            'approved_by' => null,
                            'approved_at' => null,
                        ];
                    }

                    $batch[] = $row;

                    if (count($batch) >= self::BATCH_SIZE) {
                        DB::table('period_closings')->insert($batch);
                        $batch = [];
                    }
                }
            }

            if (($index + 1) % 300 === 0) {
                $this->command->info("  PeriodClosings: " . ($index + 1) . "/$total turmas...");
            }
        }

        if (! empty($batch)) {
            DB::table('period_closings')->insert($batch);
        }

        $this->command->info("  PeriodClosings: $total/$total turmas finalizadas.");
    }
}
