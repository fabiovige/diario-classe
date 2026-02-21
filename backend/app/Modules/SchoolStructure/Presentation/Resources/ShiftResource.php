<?php

namespace App\Modules\SchoolStructure\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\SchoolStructure\Domain\Entities\Shift */
class ShiftResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'name' => $this->name->value,
            'name_label' => $this->name->label(),
            'start_time' => $this->start_time?->format('H:i'),
            'end_time' => $this->end_time?->format('H:i'),
            'school' => new SchoolResource($this->whenLoaded('school')),
        ];
    }
}
