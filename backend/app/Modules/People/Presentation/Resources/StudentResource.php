<?php

namespace App\Modules\People\Presentation\Resources;

use App\Modules\Enrollment\Domain\Enums\ClassAssignmentStatus;
use App\Modules\Enrollment\Domain\Enums\EnrollmentStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\People\Domain\Entities\Student */
class StudentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'social_name' => $this->social_name,
            'display_name' => $this->displayName(),
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'gender' => $this->gender,
            'race_color' => $this->race_color,
            'cpf' => $this->cpf,
            'has_disability' => $this->has_disability,
            'disability_type' => $this->disability_type?->value,
            'disability_type_label' => $this->disability_type?->label(),
            'active' => $this->active,
            'guardians' => GuardianResource::collection($this->whenLoaded('guardians')),
            'current_enrollment' => $this->whenLoaded('enrollments', fn () => $this->resolveCurrentEnrollment()),
            'created_at' => $this->created_at,
        ];
    }

    /** @return array{school_name: string, class_group_label: string}|null */
    private function resolveCurrentEnrollment(): ?array
    {
        $enrollment = $this->enrollments
            ->firstWhere('status', EnrollmentStatus::Active);

        if (! $enrollment) {
            return null;
        }

        $assignment = $enrollment->classAssignments
            ->firstWhere('status', ClassAssignmentStatus::Active);

        $classGroup = $assignment?->classGroup;

        $classGroupLabel = $classGroup
            ? "{$classGroup->gradeLevel->name} - {$classGroup->name} - {$classGroup->shift->name->label()}"
            : null;

        return [
            'school_name' => $enrollment->school?->name,
            'class_group_label' => $classGroupLabel,
        ];
    }
}
