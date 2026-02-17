<?php

namespace App\Modules\ClassRecord\Application\UseCases;

use App\Modules\ClassRecord\Application\DTOs\CreateLessonRecordDTO;
use App\Modules\ClassRecord\Domain\Entities\LessonRecord;

final class CreateLessonRecordUseCase
{
    public function execute(CreateLessonRecordDTO $dto): LessonRecord
    {
        return LessonRecord::create([
            'class_group_id' => $dto->classGroupId,
            'teacher_assignment_id' => $dto->teacherAssignmentId,
            'date' => $dto->date,
            'content' => $dto->content,
            'methodology' => $dto->methodology,
            'observations' => $dto->observations,
            'class_count' => $dto->classCount,
            'recorded_by' => auth()->id(),
        ]);
    }
}
