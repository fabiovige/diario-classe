<?php

namespace App\Modules\People\Presentation\Resources;

use App\Modules\Identity\Presentation\Resources\UserResource;
use App\Modules\SchoolStructure\Presentation\Resources\SchoolResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\People\Domain\Entities\Teacher */
class TeacherResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'school_id' => $this->school_id,
            'registration_number' => $this->registration_number,
            'specialization' => $this->specialization,
            'hire_date' => $this->hire_date?->format('Y-m-d'),
            'active' => $this->active,
            'user' => new UserResource($this->whenLoaded('user')),
            'school' => new SchoolResource($this->whenLoaded('school')),
        ];
    }
}
