<?php

namespace App\Modules\Assessment\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Assessment\Domain\Entities\AssessmentConfig */
class AssessmentConfigResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'academic_year_id' => $this->academic_year_id,
            'grade_level_id' => $this->grade_level_id,
            'grade_type' => $this->grade_type,
            'scale_min' => $this->scale_min,
            'scale_max' => $this->scale_max,
            'passing_grade' => $this->passing_grade,
            'average_formula' => $this->average_formula,
            'rounding_precision' => $this->rounding_precision,
            'recovery_enabled' => $this->recovery_enabled,
            'recovery_replaces' => $this->recovery_replaces,
            'conceptual_scales' => ConceptualScaleResource::collection($this->whenLoaded('conceptualScales')),
            'instruments' => AssessmentInstrumentResource::collection($this->whenLoaded('instruments')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
