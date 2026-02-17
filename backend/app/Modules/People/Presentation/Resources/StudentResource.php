<?php

namespace App\Modules\People\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\People\Domain\Entities\Student */
class StudentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'social_name' => $this->social_name,
            'display_name' => $this->displayName(),
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'gender' => $this->gender,
            'race_color' => $this->race_color,
            'cpf' => $this->cpf,
            'has_disability' => $this->has_disability,
            'active' => $this->active,
            'guardians' => GuardianResource::collection($this->whenLoaded('guardians')),
            'created_at' => $this->created_at,
        ];
    }
}
