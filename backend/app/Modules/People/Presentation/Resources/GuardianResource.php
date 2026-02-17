<?php

namespace App\Modules\People\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\People\Domain\Entities\Guardian */
class GuardianResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cpf' => $this->cpf,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'occupation' => $this->occupation,
            'relationship' => $this->whenPivotLoaded('student_guardian', fn () => $this->resource->pivot->relationship),
            'is_primary' => $this->whenPivotLoaded('student_guardian', fn () => $this->resource->pivot->is_primary),
            'students' => StudentResource::collection($this->whenLoaded('students')),
        ];
    }
}
