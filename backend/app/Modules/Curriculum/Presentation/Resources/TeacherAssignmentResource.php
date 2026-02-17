<?php

namespace App\Modules\Curriculum\Presentation\Resources;

use App\Modules\People\Presentation\Resources\TeacherResource;
use App\Modules\SchoolStructure\Presentation\Resources\ClassGroupResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Curriculum\Domain\Entities\TeacherAssignment */
class TeacherAssignmentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'teacher_id' => $this->teacher_id,
            'class_group_id' => $this->class_group_id,
            'curricular_component_id' => $this->curricular_component_id,
            'experience_field_id' => $this->experience_field_id,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'active' => $this->active,
            'teacher' => new TeacherResource($this->whenLoaded('teacher')),
            'class_group' => new ClassGroupResource($this->whenLoaded('classGroup')),
            'curricular_component' => new CurricularComponentResource($this->whenLoaded('curricularComponent')),
            'experience_field' => new ExperienceFieldResource($this->whenLoaded('experienceField')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
