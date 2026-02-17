<?php

namespace App\Modules\Identity\Application\DTOs;

final readonly class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}
}
