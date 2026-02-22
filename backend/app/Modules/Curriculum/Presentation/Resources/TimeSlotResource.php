<?php

namespace App\Modules\Curriculum\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\Curriculum\Domain\Entities\TimeSlot
 * @property \App\Modules\Curriculum\Domain\Enums\TimeSlotType $type
 */
class TimeSlotResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shift_id' => $this->shift_id,
            'number' => $this->number,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'type' => $this->type,
            'type_label' => $this->type->label(),
            'shift' => new \App\Modules\SchoolStructure\Presentation\Resources\ShiftResource($this->whenLoaded('shift')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
