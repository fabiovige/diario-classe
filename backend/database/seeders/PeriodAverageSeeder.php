<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeriodAverageSeeder extends Seeder
{
    private const BATCH_SIZE = 5000;

    public function run(): void
    {
        DB::disableQueryLog();

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
            ->get()
            ->keyBy('academic_year_id');

        $instrumentWeights = DB::table('assessment_instruments')
            ->pluck('weight', 'id');

        $teacherAssignments = DB::table('teacher_assignments')
            ->where('teacher_assignments.active', true)
            ->whereIn('teacher_assignments.class_group_id', $classGroups->pluck('id'))
            ->select('id', 'class_group_id')
            ->get()
            ->groupBy('class_group_id');

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
            $period = $firstPeriods->get($cg->academic_year_id);

            if (! $period) {
                continue;
            }

            $tas = $teacherAssignments->get($cg->id);
            $students = $studentsByClass->get($cg->id);

            if (! $tas || ! $students) {
                continue;
            }

            $studentIds = $students->pluck('student_id')->toArray();
            $taIds = $tas->pluck('id')->toArray();

            $allGrades = DB::table('grades')
                ->where('class_group_id', $cg->id)
                ->where('assessment_period_id', $period->id)
                ->whereIn('student_id', $studentIds)
                ->whereIn('teacher_assignment_id', $taIds)
                ->get()
                ->groupBy(fn ($g) => "$g->teacher_assignment_id|$g->student_id");

            $attendanceCounts = DB::table('attendance_records')
                ->select(
                    'teacher_assignment_id',
                    'student_id',
                    DB::raw('COUNT(*) as total'),
                    DB::raw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count"),
                )
                ->where('class_group_id', $cg->id)
                ->whereIn('teacher_assignment_id', $taIds)
                ->whereIn('student_id', $studentIds)
                ->whereBetween('date', [$period->start_date, $period->end_date])
                ->groupBy('teacher_assignment_id', 'student_id')
                ->get()
                ->keyBy(fn ($r) => "$r->teacher_assignment_id|$r->student_id");

            foreach ($tas as $ta) {
                foreach ($studentIds as $studentId) {
                    $key = "$ta->id|$studentId";
                    $grades = $allGrades->get($key, collect());

                    $weightedSum = 0.0;
                    $totalWeight = 0.0;

                    foreach ($grades as $grade) {
                        if ($grade->numeric_value === null) {
                            continue;
                        }
                        $w = (float) ($instrumentWeights[$grade->assessment_instrument_id] ?? 1);
                        $weightedSum += (float) $grade->numeric_value * $w;
                        $totalWeight += $w;
                    }

                    $numericAverage = $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : 0.00;

                    $att = $attendanceCounts->get($key);
                    $totalAtt = $att ? (int) $att->total : 0;
                    $absentAtt = $att ? (int) $att->absent_count : 0;

                    $frequencyPercentage = $totalAtt > 0
                        ? round(($totalAtt - $absentAtt) / $totalAtt * 100, 2)
                        : 100.00;

                    $batch[] = [
                        'student_id' => $studentId,
                        'class_group_id' => $cg->id,
                        'teacher_assignment_id' => $ta->id,
                        'assessment_period_id' => $period->id,
                        'numeric_average' => $numericAverage,
                        'conceptual_average' => null,
                        'total_absences' => $absentAtt,
                        'frequency_percentage' => $frequencyPercentage,
                        'calculated_at' => $now,
                    ];

                    if (count($batch) >= self::BATCH_SIZE) {
                        DB::table('period_averages')->insert($batch);
                        $batch = [];
                    }
                }
            }

            if (($index + 1) % 100 === 0) {
                $this->command->info("  PeriodAverages: " . ($index + 1) . "/$total turmas...");
            }
        }

        if (! empty($batch)) {
            DB::table('period_averages')->insert($batch);
        }

        $this->command->info("  PeriodAverages: $total/$total turmas finalizadas.");
    }
}
