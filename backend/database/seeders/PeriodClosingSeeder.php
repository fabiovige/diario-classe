<?php

namespace Database\Seeders;

use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\Enrollment\Domain\Entities\ClassAssignment;
use App\Modules\PeriodClosing\Domain\Entities\PeriodClosing;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use Illuminate\Database\Seeder;

class PeriodClosingSeeder extends Seeder
{
    public function run(): void
    {
        $admin = \App\Models\User::where('email', 'admin@jandira.sp.gov.br')->first();

        $classGroupIds = ClassAssignment::where('status', 'active')
            ->distinct()
            ->pluck('class_group_id')
            ->toArray();

        foreach ($classGroupIds as $classGroupId) {
            $this->processClassGroup($classGroupId, $admin);
        }
    }

    private function processClassGroup(int $classGroupId, ?\App\Models\User $admin): void
    {
        $classGroup = ClassGroup::with('academicYear')->find($classGroupId);

        if (! $classGroup) {
            return;
        }

        $periods = AssessmentPeriod::where('academic_year_id', $classGroup->academicYear->id)
            ->whereIn('number', [1, 2])
            ->get()
            ->keyBy('number');

        $teacherAssignments = TeacherAssignment::where('class_group_id', $classGroupId)
            ->where('active', true)
            ->get();

        foreach ($teacherAssignments as $teacherAssignment) {
            foreach ($periods as $periodNumber => $period) {
                $data = $this->resolveClosingData($periodNumber, $admin);

                PeriodClosing::updateOrCreate(
                    [
                        'class_group_id' => $classGroupId,
                        'teacher_assignment_id' => $teacherAssignment->id,
                        'assessment_period_id' => $period->id,
                    ],
                    $data,
                );
            }
        }
    }

    private function resolveClosingData(int $periodNumber, ?\App\Models\User $admin): array
    {
        if ($periodNumber === 1) {
            return [
                'status' => 'approved',
                'all_grades_complete' => true,
                'all_attendance_complete' => true,
                'all_lesson_records_complete' => true,
                'submitted_by' => $admin?->id,
                'submitted_at' => '2026-04-18',
                'validated_by' => $admin?->id,
                'validated_at' => '2026-04-19',
                'approved_by' => $admin?->id,
                'approved_at' => '2026-04-20',
            ];
        }

        return [
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
}
