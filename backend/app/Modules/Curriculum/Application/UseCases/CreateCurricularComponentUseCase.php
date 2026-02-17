<?php

namespace App\Modules\Curriculum\Application\UseCases;

use App\Modules\Curriculum\Application\DTOs\CreateCurricularComponentDTO;
use App\Modules\Curriculum\Domain\Entities\CurricularComponent;

final class CreateCurricularComponentUseCase
{
    public function execute(CreateCurricularComponentDTO $dto): CurricularComponent
    {
        return CurricularComponent::create([
            'name' => $dto->name,
            'knowledge_area' => $dto->knowledgeArea,
            'code' => $dto->code,
            'active' => true,
        ]);
    }
}
