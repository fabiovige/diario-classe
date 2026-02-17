<?php

namespace App\Modules\SchoolStructure\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\SchoolStructure\Domain\Entities\ClassGroup */
class ClassGroupResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'grade_level_id' => $this->grade_level_id,
            'shift_id' => $this->shift_id,
            'name' => $this->name,
            'max_students' => $this->max_students,
            'academic_year' => new AcademicYearResource($this->whenLoaded('academicYear')),
            'grade_level' => new GradeLevelResource($this->whenLoaded('gradeLevel')),
            'shift' => new ShiftResource($this->whenLoaded('shift')),
        ];
    }
}
