<?php

namespace App\Modules\Identity\Application\DTOs;

final readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $cpf = null,
        public ?int $roleId = null,
        public ?int $schoolId = null,
    ) {}
}
