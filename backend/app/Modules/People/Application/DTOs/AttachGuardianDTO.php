<?php

namespace App\Modules\People\Application\DTOs;

final readonly class AttachGuardianDTO
{
    public function __construct(
        public int $studentId,
        public int $guardianId,
        public string $relationship,
        public bool $isPrimary = false,
        public bool $canPickup = true,
    ) {}
}
