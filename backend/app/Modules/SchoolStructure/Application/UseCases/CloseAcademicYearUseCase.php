<?php

namespace App\Modules\SchoolStructure\Application\UseCases;

use App\Modules\Enrollment\Domain\Entities\ClassAssignment;
use App\Modules\PeriodClosing\Domain\Enums\ClosingStatus;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Enums\AcademicYearStatus;
use Illuminate\Support\Facades\DB;
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

        $pendingByTeacher = DB::table('period_closings as pc')
            ->join('teacher_assignments as ta', 'pc.teacher_assignment_id', '=', 'ta.id')
            ->join('teachers as t', 'ta.teacher_id', '=', 't.id')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->join('class_groups as cg', 'pc.class_group_id', '=', 'cg.id')
            ->join('assessment_periods as ap', 'pc.assessment_period_id', '=', 'ap.id')
            ->leftJoin('curricular_components as cc', 'ta.curricular_component_id', '=', 'cc.id')
            ->leftJoin('experience_fields as ef', 'ta.experience_field_id', '=', 'ef.id')
            ->whereIn('pc.class_group_id', $classGroupIds)
            ->where('pc.status', '!=', ClosingStatus::Closed->value)
            ->select([
                'u.name as teacher_name',
                'cg.name as class_group_name',
                DB::raw('COALESCE(cc.name, ef.name) as subject_name'),
                'ap.name as period_name',
            ])
            ->orderBy('u.name')
            ->get();

        if ($pendingByTeacher->isEmpty()) {
            return;
        }

        $grouped = $pendingByTeacher->groupBy('teacher_name')->map(function ($items, $teacherName) {
            $details = $items->map(fn ($i) => "{$i->class_group_name} - {$i->subject_name} - {$i->period_name}")->values()->all();

            return [
                'teacher_name' => $teacherName,
                'count' => $items->count(),
                'details' => $details,
            ];
        })->values()->all();

        throw ValidationException::withMessages([
            'period_closings' => ["Existem {$pendingByTeacher->count()} fechamento(s) de periodo ainda nao concluido(s)."],
            'pending_closings_by_teacher' => $grouped,
        ]);
    }

    private function ensureAllStudentsHaveResults(AcademicYear $academicYear): void
    {
        $classGroupIds = $academicYear->classGroups->pluck('id');

        $studentsPerGroup = DB::table('class_assignments')
            ->join('enrollments', 'class_assignments.enrollment_id', '=', 'enrollments.id')
            ->whereIn('class_assignments.class_group_id', $classGroupIds)
            ->where('class_assignments.status', 'active')
            ->where('enrollments.status', 'active')
            ->select([
                'class_assignments.class_group_id',
                DB::raw('COUNT(DISTINCT enrollments.student_id) as total'),
            ])
            ->groupBy('class_assignments.class_group_id')
            ->get()
            ->keyBy('class_group_id');

        $resultsPerGroup = DB::table('final_results')
            ->where('academic_year_id', $academicYear->id)
            ->whereIn('class_group_id', $classGroupIds)
            ->select([
                'class_group_id',
                DB::raw('COUNT(DISTINCT student_id) as total'),
            ])
            ->groupBy('class_group_id')
            ->get()
            ->keyBy('class_group_id');

        $classGroupNames = DB::table('class_groups')
            ->whereIn('id', $classGroupIds)
            ->pluck('name', 'id');

        $missingByGroup = [];

        foreach ($studentsPerGroup as $groupId => $data) {
            $resultRow = $resultsPerGroup->get($groupId);
            $resultsCount = $resultRow !== null ? (int) $resultRow->total : 0;
            $missing = (int) $data->total - (int) $resultsCount;

            if ($missing <= 0) {
                continue;
            }

            $missingByGroup[] = [
                'class_group' => $classGroupNames->get($groupId, ''),
                'count' => $missing,
            ];
        }

        if (empty($missingByGroup)) {
            return;
        }

        $totalMissing = array_sum(array_column($missingByGroup, 'count'));

        throw ValidationException::withMessages([
            'final_results' => ["{$totalMissing} aluno(s) ainda nao possuem resultado final calculado."],
            'students_without_results' => $missingByGroup,
        ]);
    }
}
