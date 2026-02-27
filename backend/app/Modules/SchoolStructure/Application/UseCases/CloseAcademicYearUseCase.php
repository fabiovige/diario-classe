<?php

namespace App\Modules\SchoolStructure\Application\UseCases;

use App\Modules\PeriodClosing\Domain\Entities\FinalResultRecord;
use App\Modules\PeriodClosing\Domain\Entities\PeriodClosing;
use App\Modules\PeriodClosing\Domain\Enums\ClosingStatus;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Enums\AcademicYearStatus;
use Illuminate\Validation\ValidationException;

final class CloseAcademicYearUseCase
{
    public function execute(int $academicYearId): AcademicYear
    {
        $academicYear = AcademicYear::with('classGroups')->findOrFail($academicYearId);

        $this->ensureNotAlreadyClosed($academicYear);
        $this->ensureAllPeriodsClosed($academicYear);
        $this->ensureAllStudentsHaveResults($academicYear);

        $academicYear->update(['status' => AcademicYearStatus::Closed]);

        return $academicYear->refresh()->load('school');
    }

    private function ensureNotAlreadyClosed(AcademicYear $academicYear): void
    {
        /** @var string $status */
        $status = $academicYear->getRawOriginal('status');

        if ($status === AcademicYearStatus::Closed->value) {
            throw ValidationException::withMessages([
                'academic_year' => ['Este ano letivo ja esta encerrado.'],
            ]);
        }
    }

    private function ensureAllPeriodsClosed(AcademicYear $academicYear): void
    {
        $classGroupIds = $academicYear->classGroups->pluck('id');

        $openClosings = PeriodClosing::whereIn('class_group_id', $classGroupIds)
            ->where('status', '!=', ClosingStatus::Closed)
            ->count();

        if ($openClosings > 0) {
            throw ValidationException::withMessages([
                'period_closings' => ["Existem {$openClosings} fechamento(s) de periodo ainda nao concluido(s)."],
            ]);
        }
    }

    private function ensureAllStudentsHaveResults(AcademicYear $academicYear): void
    {
        $classGroupIds = $academicYear->classGroups->pluck('id');

        $studentsInClasses = \App\Modules\Enrollment\Domain\Entities\ClassAssignment::whereIn('class_assignments.class_group_id', $classGroupIds)
            ->where('class_assignments.status', 'active')
            ->join('enrollments', 'class_assignments.enrollment_id', '=', 'enrollments.id')
            ->where('enrollments.status', 'active')
            ->distinct()
            ->count('enrollments.student_id');

        $studentsWithResults = FinalResultRecord::where('academic_year_id', $academicYear->id)
            ->whereIn('class_group_id', $classGroupIds)
            ->distinct()
            ->count('student_id');

        if ($studentsWithResults < $studentsInClasses) {
            $pending = $studentsInClasses - $studentsWithResults;
            throw ValidationException::withMessages([
                'final_results' => ["{$pending} aluno(s) ainda nao possuem resultado final calculado."],
            ]);
        }
    }
}
