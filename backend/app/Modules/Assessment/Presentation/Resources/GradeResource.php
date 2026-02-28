<?php

namespace App\Modules\Assessment\Presentation\Resources;

use App\Modules\AcademicCalendar\Presentation\Resources\AssessmentPeriodResource;
use App\Modules\Curriculum\Presentation\Resources\TeacherAssignmentResource;
use App\Modules\People\Presentation\Resources\StudentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Assessment\Domain\Entities\Grade */
class GradeResource extends JsonResource
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
            'assessment_instrument_id' => $this->assessment_instrument_id,
            'numeric_value' => $this->numeric_value,
            'conceptual_value' => $this->conceptual_value,
            'observations' => $this->observations,
            'is_recovery' => $this->is_recovery,
            'recovery_type' => $this->recovery_type,
            'recorded_by' => $this->recorded_by,
            'student' => new StudentResource($this->whenLoaded('student')),
            'teacher_assignment' => new TeacherAssignmentResource($this->whenLoaded('teacherAssignment')),
            'assessment_period' => new AssessmentPeriodResource($this->whenLoaded('assessmentPeriod')),
            'assessment_instrument' => new AssessmentInstrumentResource($this->whenLoaded('assessmentInstrument')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
