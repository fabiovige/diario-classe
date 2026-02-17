<?php

namespace App\Modules\Curriculum\Application\DTOs;

final readonly class CreateExperienceFieldDTO
{
    public function __construct(
        public string $name,
        public ?string $code = null,
    ) {}
}
