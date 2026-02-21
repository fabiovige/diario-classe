<?php

namespace App\Modules\Enrollment\Presentation\Resources;

use App\Modules\SchoolStructure\Presentation\Resources\SchoolResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Enrollment\Domain\Entities\EnrollmentMovement */
class EnrollmentMovementResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'enrollment_id' => $this->enrollment_id,
            'type' => $this->type,
            'movement_date' => $this->movement_date?->format('Y-m-d'),
            'reason' => $this->reason,
            'origin_school_id' => $this->origin_school_id,
            'destination_school_id' => $this->destination_school_id,
            'origin_school' => new SchoolResource($this->whenLoaded('originSchool')),
            'destination_school' => new SchoolResource($this->whenLoaded('destinationSchool')),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
        ];
    }
}
