<?php

namespace Database\Seeders;

use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\Assessment\Domain\Entities\AssessmentConfig;
use App\Modules\Assessment\Domain\Entities\AssessmentInstrument;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\Enrollment\Domain\Entities\ClassAssignment;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\SchoolStructure\Domain\Entities\GradeLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradeSeeder extends Seeder
{
    private const BATCH_SIZE = 500;

    public function run(): void
    {
        $infantilIds = GradeLevel::where('type', 'early_childhood')->pluck('id')->toArray();

        $classGroupIds = ClassAssignment::where('status', 'active')
            ->distinct()
            ->pluck('class_group_id')
            ->toArray();

        $classGroups = ClassGroup::whereIn('id', $classGroupIds)
            ->with('academicYear')
            ->get();

        $now = now()->toDateTimeString();
        $batch = [];

        foreach ($classGroups as $classGroup) {
            if (in_array($classGroup->grade_level_id, $infantilIds, true)) {
                continue;
            }

            $config = AssessmentConfig::where('school_id', $classGroup->academicYear->school_id)
                ->where('academic_year_id', $classGroup->academic_year_id)
                ->where('grade_level_id', $classGroup->grade_level_id)
                ->first();

            if (! $config) {
                continue;
            }

            $instruments = AssessmentInstrument::where('assessment_config_id', $config->id)
                ->orderBy('order')
                ->get();

            $periods = AssessmentPeriod::where('academic_year_id', $classGroup->academic_year_id)
                ->whereIn('number', [1, 2])
                ->get();

            $studentIds = DB::table('class_assignments')
                ->join('enrollments', 'enrollments.id', '=', 'class_assignments.enrollment_id')
                ->where('class_assignments.class_group_id', $classGroup->id)
                ->where('class_assignments.status', 'active')
                ->pluck('enrollments.student_id')
                ->toArray();

            $teacherAssignments = TeacherAssignment::where('class_group_id', $classGroup->id)
                ->where('active', true)
                ->with('teacher')
                ->get();

            foreach ($teacherAssignments as $teacherAssignment) {
                $recordedBy = $teacherAssignment->teacher->user_id;

                foreach ($periods as $period) {
                    foreach ($studentIds as $studentId) {
                        foreach ($instruments as $instrument) {
                            $numericValue = max(0, min(10, round(rand(40, 90) / 10 + (rand(-10, 10) / 10), 1)));

                            $batch[] = [
                                'student_id' => $studentId,
                                'class_group_id' => $classGroup->id,
                                'teacher_assignment_id' => $teacherAssignment->id,
                                'assessment_period_id' => $period->id,
                                'assessment_instrument_id' => $instrument->id,
                                'numeric_value' => $numericValue,
                                'conceptual_value' => null,
                                'observations' => null,
                                'is_recovery' => false,
                                'recovery_type' => null,
                                'recorded_by' => $recordedBy,
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
            }
        }

        if (! empty($batch)) {
            DB::table('grades')->insert($batch);
        }
    }
}
