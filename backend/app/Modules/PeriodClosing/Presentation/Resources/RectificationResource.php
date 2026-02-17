<?php

namespace App\Modules\PeriodClosing\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\PeriodClosing\Domain\Entities\Rectification */
class RectificationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'period_closing_id' => $this->period_closing_id,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'field_changed' => $this->field_changed,
            'old_value' => $this->old_value,
            'new_value' => $this->new_value,
            'justification' => $this->justification,
            'requested_by' => $this->requested_by,
            'approved_by' => $this->approved_by,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
