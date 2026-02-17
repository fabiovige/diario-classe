<?php

namespace App\Modules\Curriculum\Application\DTOs;

final readonly class CreateTeacherAssignmentDTO
{
    public function __construct(
        public int $teacherId,
        public int $classGroupId,
        public ?int $curricularComponentId,
        public ?int $experienceFieldId,
        public string $startDate,
        public ?string $endDate = null,
    ) {}
}
