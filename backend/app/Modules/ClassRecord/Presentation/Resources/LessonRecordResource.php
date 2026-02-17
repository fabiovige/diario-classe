<?php

namespace App\Modules\ClassRecord\Presentation\Resources;

use App\Modules\Curriculum\Presentation\Resources\TeacherAssignmentResource;
use App\Modules\SchoolStructure\Presentation\Resources\ClassGroupResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\ClassRecord\Domain\Entities\LessonRecord */
class LessonRecordResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'class_group_id' => $this->class_group_id,
            'teacher_assignment_id' => $this->teacher_assignment_id,
            'date' => $this->date?->format('Y-m-d'),
            'content' => $this->content,
            'methodology' => $this->methodology,
            'observations' => $this->observations,
            'class_count' => $this->class_count,
            'recorded_by' => $this->recorded_by,
            'class_group' => new ClassGroupResource($this->whenLoaded('classGroup')),
            'teacher_assignment' => new TeacherAssignmentResource($this->whenLoaded('teacherAssignment')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
