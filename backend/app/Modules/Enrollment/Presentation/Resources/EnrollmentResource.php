<?php

namespace App\Modules\Enrollment\Presentation\Resources;

use App\Modules\People\Presentation\Resources\StudentResource;
use App\Modules\SchoolStructure\Presentation\Resources\AcademicYearResource;
use App\Modules\SchoolStructure\Presentation\Resources\SchoolResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Enrollment\Domain\Entities\Enrollment */
class EnrollmentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'academic_year_id' => $this->academic_year_id,
            'school_id' => $this->school_id,
            'enrollment_number' => $this->enrollment_number,
            'enrollment_type' => $this->enrollment_type->value,
            'enrollment_type_label' => $this->enrollment_type->label(),
            'status' => $this->status,
            'enrollment_date' => $this->enrollment_date?->format('Y-m-d'),
            'exit_date' => $this->exit_date?->format('Y-m-d'),
            'student' => new StudentResource($this->whenLoaded('student')),
            'academic_year' => new AcademicYearResource($this->whenLoaded('academicYear')),
            'school' => new SchoolResource($this->whenLoaded('school')),
            'class_assignments' => ClassAssignmentResource::collection($this->whenLoaded('classAssignments')),
            'movements' => EnrollmentMovementResource::collection($this->whenLoaded('movements')),
            'documents' => EnrollmentDocumentResource::collection($this->whenLoaded('documents')),
        ];
    }
}
