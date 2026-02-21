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
            ->whereIn('number', [1, 2, 3, 4])
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

    private const APPROVED_DATES = [
        1 => ['submitted' => '2025-04-18', 'validated' => '2025-04-19', 'approved' => '2025-04-20'],
        2 => ['submitted' => '2025-07-01', 'validated' => '2025-07-02', 'approved' => '2025-07-03'],
        3 => ['submitted' => '2025-10-01', 'validated' => '2025-10-02', 'approved' => '2025-10-03'],
    ];

    private function resolveClosingData(int $periodNumber, ?\App\Models\User $admin): array
    {
        if ($periodNumber <= 3) {
            $dates = self::APPROVED_DATES[$periodNumber];

            return [
                'status' => 'approved',
                'all_grades_complete' => true,
                'all_attendance_complete' => true,
                'all_lesson_records_complete' => true,
                'submitted_by' => $admin?->id,
                'submitted_at' => $dates['submitted'],
                'validated_by' => $admin?->id,
                'validated_at' => $dates['validated'],
                'approved_by' => $admin?->id,
                'approved_at' => $dates['approved'],
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
