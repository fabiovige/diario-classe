<?php

namespace App\Modules\Curriculum\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\Curriculum\Domain\Entities\ClassSchedule
 * @property \App\Modules\Curriculum\Domain\Enums\DayOfWeek $day_of_week
 */
class ClassScheduleResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'teacher_assignment_id' => $this->teacher_assignment_id,
            'time_slot_id' => $this->time_slot_id,
            'day_of_week' => $this->day_of_week,
            'day_of_week_label' => $this->day_of_week->label(),
            'day_of_week_short' => $this->day_of_week->shortLabel(),
            'time_slot' => new TimeSlotResource($this->whenLoaded('timeSlot')),
            'teacher_assignment' => new TeacherAssignmentResource($this->whenLoaded('teacherAssignment')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
