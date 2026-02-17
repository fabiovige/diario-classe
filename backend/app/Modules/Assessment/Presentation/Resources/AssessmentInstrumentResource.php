<?php

namespace App\Modules\Assessment\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Assessment\Domain\Entities\AssessmentInstrument */
class AssessmentInstrumentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'weight' => $this->weight,
            'max_value' => $this->max_value,
            'order' => $this->order,
        ];
    }
}
