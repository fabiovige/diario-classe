<?php

namespace App\Modules\Curriculum\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\Curriculum\Domain\Entities\CurricularComponent
 * @property \App\Modules\Curriculum\Domain\Enums\KnowledgeArea $knowledge_area
 */
class CurricularComponentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'knowledge_area' => $this->knowledge_area,
            'knowledge_area_label' => $this->knowledge_area->label(),
            'code' => $this->code,
            'active' => $this->active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
