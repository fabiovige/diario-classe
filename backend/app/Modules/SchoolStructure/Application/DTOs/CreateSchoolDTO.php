<?php

namespace App\Modules\SchoolStructure\Application\DTOs;

final readonly class CreateSchoolDTO
{
    public function __construct(
        public string $name,
        public ?string $inepCode = null,
        public ?string $address = null,
        public ?string $phone = null,
        public ?string $email = null,
    ) {}
}
