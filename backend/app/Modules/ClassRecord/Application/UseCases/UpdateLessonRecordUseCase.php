<?php

namespace App\Modules\ClassRecord\Application\UseCases;

use App\Modules\ClassRecord\Application\DTOs\UpdateLessonRecordDTO;
use App\Modules\ClassRecord\Domain\Entities\LessonRecord;

final class UpdateLessonRecordUseCase
{
    public function execute(UpdateLessonRecordDTO $dto): LessonRecord
    {
        $record = LessonRecord::findOrFail($dto->id);

        $data = array_filter([
            'content' => $dto->content,
            'methodology' => $dto->methodology,
            'observations' => $dto->observations,
            'class_count' => $dto->classCount,
        ], fn ($value) => $value !== null);

        $record->update($data);

        return $record->refresh();
    }
}
