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
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'has_file' => $this->hasFile(),
            'original_filename' => $this->original_filename,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'notes' => $this->notes,
            'reviewed_by' => $this->reviewed_by,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'rejection_reason' => $this->rejection_reason,
            'can_upload' => $this->canUpload(),
            'can_review' => $this->canReview(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
