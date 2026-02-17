<?php

namespace App\Modules\Identity\Application\DTOs;

final readonly class UpdateUserDTO
{
    public function __construct(
        public int $id,
        public ?string $name = null,
        public ?string $email = null,
        public ?string $cpf = null,
        public ?string $status = null,
        public ?int $roleId = null,
        public ?int $schoolId = null,
    ) {}
}
