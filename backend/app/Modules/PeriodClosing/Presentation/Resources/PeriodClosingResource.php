<?php

namespace App\Modules\PeriodClosing\Presentation\Resources;

use App\Modules\AcademicCalendar\Presentation\Resources\AssessmentPeriodResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\PeriodClosing\Domain\Entities\PeriodClosing */
class PeriodClosingResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'class_group_id' => $this->class_group_id,
            'teacher_assignment_id' => $this->teacher_assignment_id,
            'assessment_period_id' => $this->assessment_period_id,
            'status' => $this->status,
            'submitted_by' => $this->submitted_by,
            'submitted_at' => $this->submitted_at,
            'validated_by' => $this->validated_by,
            'validated_at' => $this->validated_at,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at,
            'rejection_reason' => $this->rejection_reason,
            'all_grades_complete' => $this->all_grades_complete,
            'all_attendance_complete' => $this->all_attendance_complete,
            'all_lesson_records_complete' => $this->all_lesson_records_complete,
            'assessment_period' => new AssessmentPeriodResource($this->whenLoaded('assessmentPeriod')),
            'class_group' => $this->whenLoaded('classGroup'),
            'teacher_assignment' => $this->whenLoaded('teacherAssignment'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
