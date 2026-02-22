<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradeSeeder extends Seeder
{
    private const BATCH_SIZE = 5000;

    public function run(): void
    {
        DB::disableQueryLog();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('SET UNIQUE_CHECKS=0');

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

        $firstPeriods = DB::table('assessment_periods')
            ->where('number', 1)
            ->pluck('id', 'academic_year_id');

        $instruments = DB::table('assessment_instruments')
            ->join('assessment_configs', 'assessment_configs.id', '=', 'assessment_instruments.assessment_config_id')
            ->select(
                'assessment_instruments.id',
                'assessment_configs.school_id',
                'assessment_configs.academic_year_id',
                'assessment_configs.grade_level_id',
            )
            ->orderBy('assessment_instruments.order')
            ->get()
            ->groupBy(fn ($i) => "$i->school_id|$i->academic_year_id|$i->grade_level_id");

        $teacherAssignments = DB::table('teacher_assignments')
            ->where('teacher_assignments.active', true)
            ->join('teachers', 'teachers.id', '=', 'teacher_assignments.teacher_id')
            ->select('teacher_assignments.id', 'teacher_assignments.class_group_id', 'teachers.user_id')
            ->get()
            ->groupBy('class_group_id');

        $studentsByClass = DB::table('class_assignments')
            ->where('class_assignments.status', 'active')
            ->join('enrollments', 'enrollments.id', '=', 'class_assignments.enrollment_id')
            ->select('class_assignments.class_group_id', 'enrollments.student_id')
            ->get()
            ->groupBy('class_group_id');

        $academicYears = DB::table('academic_years')
            ->pluck('school_id', 'id');

        $now = now()->toDateTimeString();
        $batch = [];
        $total = $classGroups->count();

        foreach ($classGroups as $index => $cg) {
            $periodId = $firstPeriods->get($cg->academic_year_id);
            $schoolId = $academicYears->get($cg->academic_year_id);

            if (! $periodId || ! $schoolId) {
                continue;
            }

            $key = "$schoolId|$cg->academic_year_id|$cg->grade_level_id";
            $cgInstruments = $instruments->get($key);

            if (! $cgInstruments) {
                continue;
            }

            $tas = $teacherAssignments->get($cg->id);
            $students = $studentsByClass->get($cg->id);

            if (! $tas || ! $students) {
                continue;
            }

            $studentIds = $students->pluck('student_id')->toArray();

            foreach ($tas as $ta) {
                foreach ($studentIds as $studentId) {
                    foreach ($cgInstruments as $instrument) {
                        $batch[] = [
                            'student_id' => $studentId,
                            'class_group_id' => $cg->id,
                            'teacher_assignment_id' => $ta->id,
                            'assessment_period_id' => $periodId,
                            'assessment_instrument_id' => $instrument->id,
                            'numeric_value' => max(0, min(10, round(rand(40, 90) / 10 + (rand(-10, 10) / 10), 1))),
                            'conceptual_value' => null,
                            'observations' => null,
                            'is_recovery' => false,
                            'recovery_type' => null,
                            'recorded_by' => $ta->user_id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        if (count($batch) >= self::BATCH_SIZE) {
                            DB::table('grades')->insert($batch);
                            $batch = [];
                        }
                    }
                }
            }

            if (($index + 1) % 100 === 0) {
                $this->command->info("  Grades: " . ($index + 1) . "/$total turmas...");
            }
        }

        if (! empty($batch)) {
            DB::table('grades')->insert($batch);
        }

        DB::statement('SET UNIQUE_CHECKS=1');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info("  Grades: $total/$total turmas finalizadas.");
    }
}
