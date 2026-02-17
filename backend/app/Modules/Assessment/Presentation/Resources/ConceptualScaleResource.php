<?php

namespace App\Modules\Assessment\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Assessment\Domain\Entities\ConceptualScale */
class ConceptualScaleResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'label' => $this->label,
            'numeric_equivalent' => $this->numeric_equivalent,
            'passing' => $this->passing,
            'order' => $this->order,
        ];
    }
}
