<?php

namespace App\Modules\SchoolStructure\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\SchoolStructure\Domain\Entities\School */
class SchoolResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'inep_code' => $this->inep_code,
            'type' => $this->type,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'active' => $this->active,
            'created_at' => $this->created_at,
        ];
    }
}
