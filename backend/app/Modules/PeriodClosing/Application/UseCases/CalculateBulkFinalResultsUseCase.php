<?php

namespace App\Modules\PeriodClosing\Application\UseCases;

use App\Modules\Enrollment\Domain\Entities\ClassAssignment;
use App\Modules\PeriodClosing\Domain\Entities\FinalResultRecord;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use Illuminate\Support\Collection;

final class CalculateBulkFinalResultsUseCase
{
    public function __construct(
        private CalculateFinalResultUseCase $calculateFinalResult,
    ) {}

    /** @return Collection<int, FinalResultRecord> */
    public function execute(int $classGroupId, int $academicYearId): Collection
    {
        $classGroup = ClassGroup::findOrFail($classGroupId);

        $studentIds = ClassAssignment::where('class_group_id', $classGroup->id)
            ->where('status', 'active')
            ->join('enrollments', 'class_assignments.enrollment_id', '=', 'enrollments.id')
            ->where('enrollments.status', 'active')
            ->pluck('enrollments.student_id')
            ->unique();

        return $studentIds->map(
            fn (int $studentId) => $this->calculateFinalResult->execute(
                studentId: $studentId,
                classGroupId: $classGroupId,
                academicYearId: $academicYearId,
            )
        );
    }
}
