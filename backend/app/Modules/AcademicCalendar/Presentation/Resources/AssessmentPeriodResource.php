<?php

namespace App\Modules\AcademicCalendar\Presentation\Resources;

use App\Modules\SchoolStructure\Presentation\Resources\AcademicYearResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod */
class AssessmentPeriodResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'type' => $this->type,
            'number' => $this->number,
            'name' => $this->name,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'status' => $this->status,
            'academic_year' => new AcademicYearResource($this->whenLoaded('academicYear')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
