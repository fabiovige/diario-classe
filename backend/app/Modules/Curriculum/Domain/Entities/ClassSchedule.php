<?php

namespace App\Modules\Curriculum\Domain\Entities;

use App\Modules\Curriculum\Domain\Enums\DayOfWeek;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property DayOfWeek $day_of_week
 */
class ClassSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\ClassScheduleFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\ClassScheduleFactory
    {
        return \Database\Factories\ClassScheduleFactory::new();
    }

    protected $fillable = [
        'teacher_assignment_id',
        'time_slot_id',
        'day_of_week',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => DayOfWeek::class,
        ];
    }

    /** @return BelongsTo<TeacherAssignment, $this> */
    public function teacherAssignment(): BelongsTo
    {
        return $this->belongsTo(TeacherAssignment::class);
    }

    /** @return BelongsTo<TimeSlot, $this> */
    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }
}
