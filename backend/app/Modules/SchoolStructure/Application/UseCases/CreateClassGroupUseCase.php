<?php

namespace App\Modules\SchoolStructure\Application\UseCases;

use App\Modules\SchoolStructure\Application\DTOs\CreateClassGroupDTO;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;

final class CreateClassGroupUseCase
{
    public function execute(CreateClassGroupDTO $dto): ClassGroup
    {
        return ClassGroup::create([
            'academic_year_id' => $dto->academicYearId,
            'grade_level_id' => $dto->gradeLevelId,
            'shift_id' => $dto->shiftId,
            'name' => $dto->name,
            'max_students' => $dto->maxStudents,
        ]);
    }
}
