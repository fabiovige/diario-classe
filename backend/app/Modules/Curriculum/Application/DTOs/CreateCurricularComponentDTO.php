<?php

namespace App\Modules\Curriculum\Application\DTOs;

final readonly class CreateCurricularComponentDTO
{
    public function __construct(
        public string $name,
        public string $knowledgeArea,
        public ?string $code = null,
    ) {}
}
