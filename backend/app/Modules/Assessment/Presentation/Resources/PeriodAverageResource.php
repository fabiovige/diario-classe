<?php

namespace App\Modules\Assessment\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Assessment\Domain\Entities\PeriodAverage */
class PeriodAverageResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'class_group_id' => $this->class_group_id,
            'teacher_assignment_id' => $this->teacher_assignment_id,
            'assessment_period_id' => $this->assessment_period_id,
            'numeric_average' => $this->numeric_average,
            'conceptual_average' => $this->conceptual_average,
            'total_absences' => $this->total_absences,
            'frequency_percentage' => $this->frequency_percentage,
            'calculated_at' => $this->calculated_at,
        ];
    }
}
