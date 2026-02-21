<?php

namespace Database\Seeders;

use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\Assessment\Domain\Entities\AssessmentConfig;
use App\Modules\Assessment\Domain\Entities\AssessmentInstrument;
use App\Modules\Assessment\Domain\Entities\Grade;
use App\Modules\Attendance\Domain\Entities\AttendanceRecord;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\Enrollment\Domain\Entities\ClassAssignment;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\SchoolStructure\Domain\Entities\GradeLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeriodAverageSeeder extends Seeder
{
    private const BATCH_SIZE = 500;

    public function run(): void
    {
        $infantilIds = GradeLevel::where('type', 'early_childhood')->pluck('id')->toArray();

        $classGroupIds = ClassAssignment::where('status', 'active')
            ->distinct()
            ->pluck('class_group_id')
            ->toArray();

        $classGroups = ClassGroup::with('academicYear')
            ->whereIn('id', $classGroupIds)
            ->whereNotIn('grade_level_id', $infantilIds)
            ->get();

        foreach ($classGroups as $classGroup) {
            $this->processClassGroup($classGroup, $infantilIds);
        }
    }

    private function processClassGroup(ClassGroup $classGroup, array $infantilIds): void
    {
        $academicYear = $classGroup->academicYear;

        $config = AssessmentConfig::where('school_id', $academicYear->school_id)
            ->where('academic_year_id', $academicYear->id)
            ->where('grade_level_id', $classGroup->grade_level_id)
            ->first();

        if (! $config) {
            return;
        }

        $instruments = AssessmentInstrument::where('assessment_config_id', $config->id)->get();

        if ($instruments->isEmpty()) {
            return;
        }

        $periods = AssessmentPeriod::where('academic_year_id', $academicYear->id)
            ->whereIn('number', [1, 2, 3, 4])
            ->get()
            ->keyBy('number');

        $studentIds = DB::table('class_assignments')
            ->join('enrollments', 'enrollments.id', '=', 'class_assignments.enrollment_id')
            ->where('class_assignments.class_group_id', $classGroup->id)
            ->where('class_assignments.status', 'active')
            ->pluck('enrollments.student_id')
            ->toArray();

        $teacherAssignments = TeacherAssignment::where('class_group_id', $classGroup->id)
            ->where('active', true)
            ->get();

        $batch = [];

        foreach ($teacherAssignments as $teacherAssignment) {
            foreach ($periods as $period) {
                foreach ($studentIds as $studentId) {
                    $row = $this->buildAverageRow(
                        $studentId,
                        $classGroup->id,
                        $teacherAssignment->id,
                        $period,
                        $instruments
                    );

                    $batch[] = $row;

                    if (count($batch) >= self::BATCH_SIZE) {
                        DB::table('period_averages')->insert($batch);
                        $batch = [];
                    }
                }
            }
        }

        if (! empty($batch)) {
            DB::table('period_averages')->insert($batch);
        }
    }

    private function buildAverageRow(
        int $studentId,
        int $classGroupId,
        int $teacherAssignmentId,
        AssessmentPeriod $period,
        $instruments
    ): array {
        $grades = Grade::where('student_id', $studentId)
            ->where('class_group_id', $classGroupId)
            ->where('teacher_assignment_id', $teacherAssignmentId)
            ->where('assessment_period_id', $period->id)
            ->get()
            ->keyBy('assessment_instrument_id');

        $weightedSum = 0.0;
        $totalWeight = 0.0;

        foreach ($instruments as $instrument) {
            $grade = $grades->get($instrument->id);

            if (! $grade || $grade->numeric_value === null) {
                continue;
            }

            $weightedSum += (float) $grade->numeric_value * (float) $instrument->weight;
            $totalWeight += (float) $instrument->weight;
        }

        $numericAverage = $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : 0.00;

        $totalAttendance = AttendanceRecord::where('student_id', $studentId)
            ->where('class_group_id', $classGroupId)
            ->where('teacher_assignment_id', $teacherAssignmentId)
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->count();

        $absentCount = AttendanceRecord::where('student_id', $studentId)
            ->where('class_group_id', $classGroupId)
            ->where('teacher_assignment_id', $teacherAssignmentId)
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->where('status', 'absent')
            ->count();

        $frequencyPercentage = $totalAttendance > 0
            ? round(($totalAttendance - $absentCount) / $totalAttendance * 100, 2)
            : 100.00;

        return [
            'student_id' => $studentId,
            'class_group_id' => $classGroupId,
            'teacher_assignment_id' => $teacherAssignmentId,
            'assessment_period_id' => $period->id,
            'numeric_average' => $numericAverage,
            'conceptual_average' => null,
            'total_absences' => $absentCount,
            'frequency_percentage' => $frequencyPercentage,
            'calculated_at' => now(),
        ];
    }
}
