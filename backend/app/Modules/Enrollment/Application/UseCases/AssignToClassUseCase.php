<?php

namespace App\Modules\Enrollment\Application\UseCases;

use App\Modules\Enrollment\Application\DTOs\AssignToClassDTO;
use App\Modules\Enrollment\Application\DTOs\AssignToClassResult;
use App\Modules\Enrollment\Domain\Entities\ClassAssignment;
use App\Modules\Enrollment\Domain\Entities\Enrollment;
use App\Modules\Enrollment\Domain\Enums\ClassAssignmentStatus;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AssignToClassUseCase
{
    private const AGE_CUT_OFF_DATE = '03-31';

    public function execute(AssignToClassDTO $dto): AssignToClassResult
    {
        $enrollment = Enrollment::with('student')->findOrFail($dto->enrollmentId);

        if (! $enrollment->isActive()) {
            throw ValidationException::withMessages([
                'enrollment_id' => ['Matrícula não está ativa.'],
            ]);
        }

        $classGroup = ClassGroup::with('gradeLevel')->findOrFail($dto->classGroupId);
        $activeCount = ClassAssignment::where('class_group_id', $dto->classGroupId)
            ->where('status', ClassAssignmentStatus::Active->value)
            ->count();

        if ($activeCount >= $classGroup->max_students) {
            throw ValidationException::withMessages([
                'class_group_id' => ["Turma lotada ({$activeCount}/{$classGroup->max_students} alunos)."],
            ]);
        }

        $warnings = $this->checkAgeWarning($enrollment, $classGroup);

        $assignment = DB::transaction(function () use ($dto, $enrollment) {
            $enrollment->classAssignments()
                ->where('status', ClassAssignmentStatus::Active->value)
                ->update([
                    'status' => ClassAssignmentStatus::Transferred->value,
                    'end_date' => $dto->startDate,
                ]);

            return ClassAssignment::create([
                'enrollment_id' => $dto->enrollmentId,
                'class_group_id' => $dto->classGroupId,
                'status' => ClassAssignmentStatus::Active->value,
                'start_date' => $dto->startDate,
            ]);
        });

        return new AssignToClassResult($assignment, $warnings);
    }

    /** @return array<string> */
    private function checkAgeWarning(Enrollment $enrollment, ClassGroup $classGroup): array
    {
        $gradeLevel = $classGroup->gradeLevel;
        if (! $gradeLevel?->min_age_months) {
            return [];
        }

        $student = $enrollment->student;
        if (! $student?->birth_date) {
            return [];
        }

        $enrollmentYear = $enrollment->enrollment_date->year;
        $cutOffDate = Carbon::createFromFormat('Y-m-d', "{$enrollmentYear}-" . self::AGE_CUT_OFF_DATE);

        $ageInMonths = (int) $student->birth_date->diffInMonths($cutOffDate);

        if ($ageInMonths >= $gradeLevel->min_age_months) {
            return [];
        }

        $expectedYears = intdiv($gradeLevel->min_age_months, 12);
        $expectedMonths = $gradeLevel->min_age_months % 12;
        $studentYears = intdiv($ageInMonths, 12);
        $studentMonths = $ageInMonths % 12;

        return [
            "Aluno com {$studentYears} anos e {$studentMonths} meses na data de corte (31/03/{$enrollmentYear}). "
            . "Idade mínima para {$gradeLevel->name}: {$expectedYears} anos e {$expectedMonths} meses.",
        ];
    }
}
