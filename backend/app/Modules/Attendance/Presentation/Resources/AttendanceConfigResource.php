<?php

namespace App\Modules\Attendance\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Attendance\Domain\Entities\AttendanceConfig */
class AttendanceConfigResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'academic_year_id' => $this->academic_year_id,
            'consecutive_absences_alert' => $this->consecutive_absences_alert,
            'monthly_absences_alert' => $this->monthly_absences_alert,
            'period_absence_percentage_alert' => $this->period_absence_percentage_alert,
            'annual_minimum_frequency' => $this->annual_minimum_frequency,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
