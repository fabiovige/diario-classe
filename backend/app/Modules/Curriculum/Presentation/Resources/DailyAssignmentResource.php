<?php

namespace App\Modules\Curriculum\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\Curriculum\Domain\Entities\TeacherAssignment
 *
 * @property int|null $has_attendance
 * @property int|null $has_lesson_record
 * @property int|null $has_open_period
 */
class DailyAssignmentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...(new TeacherAssignmentResource($this->resource))->toArray($request),
            'has_attendance' => (bool) $this->has_attendance,
            'has_lesson_record' => (bool) $this->has_lesson_record,
            'has_open_period' => (bool) $this->has_open_period,
        ];
    }
}
