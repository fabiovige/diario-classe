<?php

namespace App\Modules\Attendance\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Attendance\Domain\Entities\AbsenceJustification */
class AbsenceJustificationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'reason' => $this->reason,
            'document_path' => $this->document_path,
            'approved' => $this->approved,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
        ];
    }
}
