<?php

namespace App\Modules\PeriodClosing\Application\UseCases;

use App\Modules\Assessment\Domain\Entities\AssessmentConfig;
use App\Modules\Assessment\Domain\Entities\PeriodAverage;
use App\Modules\Attendance\Domain\Services\FrequencyCalculator;
use App\Modules\PeriodClosing\Domain\Entities\FinalResultRecord;
use App\Modules\PeriodClosing\Domain\Enums\FinalResult;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use Illuminate\Support\Facades\DB;

final class CalculateFinalResultUseCase
{
    public function __construct(
        private FrequencyCalculator $frequencyCalculator,
    ) {}

    public function execute(
        int $studentId,
        int $classGroupId,
        int $academicYearId,
    ): FinalResultRecord {
        $classGroup = ClassGroup::with('academicYear')->findOrFail($classGroupId);

        $config = AssessmentConfig::where('school_id', $classGroup->academicYear->school_id)
            ->where('academic_year_id', $academicYearId)
            ->where('grade_level_id', $classGroup->grade_level_id)
            ->first();

        $periodAverages = PeriodAverage::where('student_id', $studentId)
            ->where('class_group_id', $classGroupId)
            ->get();

        $overallAverage = $periodAverages->avg('numeric_average');

        $frequency = $this->frequencyCalculator->calculate(
            studentId: $studentId,
            classGroupId: $classGroupId,
        );

        $result = $this->determineResult($overallAverage, $frequency['frequency_percentage'], $config);

        $attributes = [
            'result' => $result->value,
            'overall_average' => $overallAverage ? round($overallAverage, 2) : null,
            'overall_frequency' => $frequency['frequency_percentage'],
            'council_override' => false,
            'determined_by' => auth()->id(),
        ];

        $now = now();

        DB::table('final_results')->upsert(
            [array_merge([
                'student_id' => $studentId,
                'class_group_id' => $classGroupId,
                'academic_year_id' => $academicYearId,
                'created_at' => $now,
                'updated_at' => $now,
            ], $attributes)],
            ['student_id', 'class_group_id', 'academic_year_id'],
            array_merge(array_keys($attributes), ['updated_at']),
        );

        return FinalResultRecord::where('student_id', $studentId)
            ->where('class_group_id', $classGroupId)
            ->where('academic_year_id', $academicYearId)
            ->first();
    }

    private function determineResult(?float $average, float $frequency, ?AssessmentConfig $config): FinalResult
    {
        if ($frequency < 75.00) {
            return FinalResult::Retained;
        }

        if ($config === null || $average === null) {
            return FinalResult::Approved;
        }

        $passingGrade = (float) ($config->passing_grade ?? 6.0);

        if ($average >= $passingGrade) {
            return FinalResult::Approved;
        }

        return FinalResult::Retained;
    }
}
