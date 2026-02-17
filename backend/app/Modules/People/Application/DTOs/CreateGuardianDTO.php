<?php

namespace App\Modules\People\Application\DTOs;

final readonly class CreateGuardianDTO
{
    public function __construct(
        public string $name,
        public ?string $cpf = null,
        public ?string $rg = null,
        public ?string $phone = null,
        public ?string $phoneSecondary = null,
        public ?string $email = null,
        public ?string $address = null,
        public ?string $occupation = null,
    ) {}
}
