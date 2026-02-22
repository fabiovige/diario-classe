<?php

namespace App\Modules\Curriculum\Application\DTOs;

final readonly class SaveClassScheduleDTO
{
    /**
     * @param  array<array{time_slot_id: int, day_of_week: int}>  $slots
     */
    public function __construct(
        public int $teacherAssignmentId,
        public array $slots,
    ) {}
}
