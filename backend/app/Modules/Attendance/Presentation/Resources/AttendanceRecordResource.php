<?php

namespace App\Modules\Attendance\Presentation\Resources;

use App\Modules\People\Presentation\Resources\StudentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Attendance\Domain\Entities\AttendanceRecord */
class AttendanceRecordResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'class_group_id' => $this->class_group_id,
            'teacher_assignment_id' => $this->teacher_assignment_id,
            'student_id' => $this->student_id,
            'date' => $this->date?->format('Y-m-d'),
            'status' => $this->status,
            'recorded_by' => $this->recorded_by,
            'student' => new StudentResource($this->whenLoaded('student')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
