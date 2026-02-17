<?php

namespace App\Modules\Assessment\Application\UseCases;

use App\Modules\Assessment\Application\DTOs\RecordBulkGradesDTO;
use App\Modules\Assessment\Domain\Entities\Grade;
use Illuminate\Support\Facades\DB;

final class RecordBulkGradesUseCase
{
    /** @return \Illuminate\Database\Eloquent\Collection<int, Grade> */
    public function execute(RecordBulkGradesDTO $dto): \Illuminate\Database\Eloquent\Collection
    {
        return DB::transaction(function () use ($dto) {
            $now = now();

            foreach ($dto->grades as $gradeData) {
                Grade::updateOrCreate(
                    [
                        'student_id' => $gradeData['student_id'],
                        'class_group_id' => $dto->classGroupId,
                        'teacher_assignment_id' => $dto->teacherAssignmentId,
                        'assessment_period_id' => $dto->assessmentPeriodId,
                        'assessment_instrument_id' => $dto->assessmentInstrumentId,
                        'is_recovery' => false,
                    ],
                    [
                        'numeric_value' => $gradeData['numeric_value'] ?? null,
                        'conceptual_value' => $gradeData['conceptual_value'] ?? null,
                        'observations' => $gradeData['observations'] ?? null,
                        'recorded_by' => auth()->id(),
                        'updated_at' => $now,
                    ],
                );
            }

            return Grade::where('class_group_id', $dto->classGroupId)
                ->where('teacher_assignment_id', $dto->teacherAssignmentId)
                ->where('assessment_period_id', $dto->assessmentPeriodId)
                ->where('assessment_instrument_id', $dto->assessmentInstrumentId)
                ->with('student')
                ->get();
        });
    }
}
