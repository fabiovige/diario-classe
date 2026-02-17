<?php

namespace App\Modules\ClassRecord\Application\DTOs;

final readonly class CreateLessonRecordDTO
{
    public function __construct(
        public int $classGroupId,
        public int $teacherAssignmentId,
        public string $date,
        public string $content,
        public ?string $methodology = null,
        public ?string $observations = null,
        public int $classCount = 1,
    ) {}
}
