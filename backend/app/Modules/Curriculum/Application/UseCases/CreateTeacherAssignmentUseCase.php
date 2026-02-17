<?php

namespace App\Modules\Curriculum\Application\UseCases;

use App\Modules\Curriculum\Application\DTOs\CreateTeacherAssignmentDTO;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\People\Domain\Entities\Teacher;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use Illuminate\Validation\ValidationException;

final class CreateTeacherAssignmentUseCase
{
    public function execute(CreateTeacherAssignmentDTO $dto): TeacherAssignment
    {
        $this->validateComponentXorField($dto);
        $this->validateTeacherBelongsToSchool($dto);

        return TeacherAssignment::create([
            'teacher_id' => $dto->teacherId,
            'class_group_id' => $dto->classGroupId,
            'curricular_component_id' => $dto->curricularComponentId,
            'experience_field_id' => $dto->experienceFieldId,
            'start_date' => $dto->startDate,
            'end_date' => $dto->endDate,
            'active' => true,
        ]);
    }

    private function validateComponentXorField(CreateTeacherAssignmentDTO $dto): void
    {
        $hasComponent = $dto->curricularComponentId !== null;
        $hasField = $dto->experienceFieldId !== null;

        if ($hasComponent === $hasField) {
            throw ValidationException::withMessages([
                'curricular_component_id' => 'Informe exatamente um: componente curricular OU campo de experiência.',
            ]);
        }
    }

    private function validateTeacherBelongsToSchool(CreateTeacherAssignmentDTO $dto): void
    {
        $teacher = Teacher::findOrFail($dto->teacherId);
        $classGroup = ClassGroup::with('academicYear')->findOrFail($dto->classGroupId);

        if ($teacher->school_id !== $classGroup->academicYear->school_id) {
            throw ValidationException::withMessages([
                'teacher_id' => 'O professor deve pertencer à mesma escola da turma.',
            ]);
        }
    }
}
