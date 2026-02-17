<?php

namespace App\Modules\PeriodClosing\Presentation\Resources;

use App\Modules\People\Presentation\Resources\StudentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\PeriodClosing\Domain\Entities\FinalResultRecord */
class FinalResultResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'class_group_id' => $this->class_group_id,
            'academic_year_id' => $this->academic_year_id,
            'result' => $this->result,
            'overall_average' => $this->overall_average,
            'overall_frequency' => $this->overall_frequency,
            'council_override' => $this->council_override,
            'observations' => $this->observations,
            'determined_by' => $this->determined_by,
            'student' => new StudentResource($this->whenLoaded('student')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
