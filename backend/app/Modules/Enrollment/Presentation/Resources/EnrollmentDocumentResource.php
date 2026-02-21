<?php

namespace App\Modules\Enrollment\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Enrollment\Domain\Entities\EnrollmentDocument */
class EnrollmentDocumentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'enrollment_id' => $this->enrollment_id,
            'document_type' => $this->document_type->value,
            'document_type_label' => $this->document_type->label(),
            'delivered' => $this->delivered,
            'delivered_at' => $this->delivered_at?->format('Y-m-d'),
            'notes' => $this->notes,
        ];
    }
}
