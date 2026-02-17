<?php

namespace App\Modules\Enrollment\Application\DTOs;

final readonly class CreateMovementDTO
{
    public function __construct(
        public int $enrollmentId,
        public string $type,
        public string $movementDate,
        public ?string $reason = null,
        public ?int $createdBy = null,
    ) {}
}
