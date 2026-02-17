<?php

namespace App\Modules\Enrollment\Presentation\Resources;

use App\Modules\SchoolStructure\Presentation\Resources\ClassGroupResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Enrollment\Domain\Entities\ClassAssignment */
class ClassAssignmentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'enrollment_id' => $this->enrollment_id,
            'class_group_id' => $this->class_group_id,
            'status' => $this->status,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'class_group' => new ClassGroupResource($this->whenLoaded('classGroup')),
        ];
    }
}
