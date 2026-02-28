<?php

namespace App\Modules\Assessment\Presentation\Resources;

use App\Modules\SchoolStructure\Presentation\Resources\ClassGroupResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Assessment\Domain\Entities\DescriptiveReport */
class DescriptiveReportResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'class_group_id' => $this->class_group_id,
            'experience_field_id' => $this->experience_field_id,
            'assessment_period_id' => $this->assessment_period_id,
            'content' => $this->content,
            'recorded_by' => $this->recorded_by,
            'student' => $this->whenLoaded('student', fn () => [
                'id' => $this->student->id,
                'name' => $this->student->name,
            ]),
            'class_group' => new ClassGroupResource($this->whenLoaded('classGroup')),
            'experience_field' => $this->whenLoaded('experienceField', fn () => [
                'id' => $this->experienceField->id,
                'name' => $this->experienceField->name,
            ]),
            'assessment_period' => $this->whenLoaded('assessmentPeriod', fn () => [
                'id' => $this->assessmentPeriod->id,
                'name' => $this->assessmentPeriod->name,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
