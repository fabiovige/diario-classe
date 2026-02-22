<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinalResultSeeder extends Seeder
{
    private const BATCH_SIZE = 1000;

    public function run(): void
    {
        DB::disableQueryLog();

        $adminId = DB::table('users')
            ->where('email', 'admin@jandira.sp.gov.br')
            ->value('id');

        $infantilIds = DB::table('grade_levels')
            ->where('type', 'early_childhood')
            ->pluck('id')
            ->toArray();

        $classGroups = DB::table('class_groups')
            ->whereNotIn('grade_level_id', $infantilIds)
            ->whereIn('id', function ($q) {
                $q->select('class_group_id')
                    ->from('class_assignments')
                    ->where('status', 'active')
                    ->distinct();
            })
            ->get();

        $studentsByClass = DB::table('class_assignments')
            ->where('class_assignments.status', 'active')
            ->whereIn('class_assignments.class_group_id', $classGroups->pluck('id'))
            ->join('enrollments', 'enrollments.id', '=', 'class_assignments.enrollment_id')
            ->select('class_assignments.class_group_id', 'enrollments.student_id')
            ->get()
            ->groupBy('class_group_id');

        $now = now()->toDateTimeString();
        $batch = [];
        $total = $classGroups->count();

        foreach ($classGroups as $index => $cg) {
            $students = $studentsByClass->get($cg->id);

            if (! $students) {
                continue;
            }

            foreach ($students as $student) {
                $batch[] = [
                    'student_id' => $student->student_id,
                    'class_group_id' => $cg->id,
                    'academic_year_id' => $cg->academic_year_id,
                    'result' => 'in_progress',
                    'overall_average' => null,
                    'overall_frequency' => null,
                    'council_override' => false,
                    'observations' => null,
                    'determined_by' => $adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($batch) >= self::BATCH_SIZE) {
                    DB::table('final_results')->insert($batch);
                    $batch = [];
                }
            }

            if (($index + 1) % 200 === 0) {
                $this->command->info("  FinalResults: " . ($index + 1) . "/$total turmas...");
            }
        }

        if (! empty($batch)) {
            DB::table('final_results')->insert($batch);
        }

        $this->command->info("  FinalResults: $total/$total turmas finalizadas.");
    }
}
