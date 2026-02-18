<?php

namespace Database\Seeders;

use App\Modules\Assessment\Domain\Entities\PeriodAverage;
use App\Modules\Enrollment\Domain\Entities\ClassAssignment;
use App\Modules\PeriodClosing\Domain\Entities\FinalResultRecord;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\SchoolStructure\Domain\Entities\GradeLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinalResultSeeder extends Seeder
{
    private const PASSING_AVERAGE = 6.0;

    private const MINIMUM_FREQUENCY = 75.0;

    public function run(): void
    {
        $admin = \App\Models\User::where('email', 'admin@jandira.sp.gov.br')->first();

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
            $this->processClassGroup($classGroup, $admin);
        }
    }

    private function processClassGroup(ClassGroup $classGroup, ?\App\Models\User $admin): void
    {
        $studentIds = DB::table('class_assignments')
            ->join('enrollments', 'enrollments.id', '=', 'class_assignments.enrollment_id')
            ->where('class_assignments.class_group_id', $classGroup->id)
            ->where('class_assignments.status', 'active')
            ->pluck('enrollments.student_id')
            ->toArray();

        foreach ($studentIds as $studentId) {
            $this->processStudent($studentId, $classGroup, $admin);
        }
    }

    private function processStudent(int $studentId, ClassGroup $classGroup, ?\App\Models\User $admin): void
    {
        $periodAverages = PeriodAverage::where('student_id', $studentId)
            ->where('class_group_id', $classGroup->id)
            ->get();

        if ($periodAverages->isEmpty()) {
            return;
        }

        $overallAverage = round($periodAverages->avg('numeric_average'), 2);
        $overallFrequency = round($periodAverages->avg('frequency_percentage'), 2);

        $result = $this->determineResult($overallAverage, $overallFrequency);

        FinalResultRecord::updateOrCreate(
            [
                'student_id' => $studentId,
                'class_group_id' => $classGroup->id,
                'academic_year_id' => $classGroup->academicYear->id,
            ],
            [
                'result' => $result,
                'overall_average' => $overallAverage,
                'overall_frequency' => $overallFrequency,
                'council_override' => false,
                'observations' => null,
                'determined_by' => $admin?->id,
            ],
        );
    }

    private function determineResult(float $overallAverage, float $overallFrequency): string
    {
        if ($overallFrequency < self::MINIMUM_FREQUENCY) {
            return 'retained';
        }

        if ($overallAverage >= self::PASSING_AVERAGE) {
            return 'approved';
        }

        return 'retained';
    }
}
