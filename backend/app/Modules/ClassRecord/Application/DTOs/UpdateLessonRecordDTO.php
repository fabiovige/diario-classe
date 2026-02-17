<?php

namespace App\Modules\ClassRecord\Application\DTOs;

final readonly class UpdateLessonRecordDTO
{
    public function __construct(
        public int $id,
        public ?string $content = null,
        public ?string $methodology = null,
        public ?string $observations = null,
        public ?int $classCount = null,
    ) {}
}
