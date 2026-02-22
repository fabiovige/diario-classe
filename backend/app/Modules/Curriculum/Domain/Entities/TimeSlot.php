<?php

namespace App\Modules\Curriculum\Domain\Entities;

use App\Modules\Curriculum\Domain\Enums\TimeSlotType;
use App\Modules\SchoolStructure\Domain\Entities\Shift;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property TimeSlotType $type
 */
class TimeSlot extends Model
{
    /** @use HasFactory<\Database\Factories\TimeSlotFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\TimeSlotFactory
    {
        return \Database\Factories\TimeSlotFactory::new();
    }

    protected $fillable = [
        'shift_id',
        'number',
        'start_time',
        'end_time',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'type' => TimeSlotType::class,
        ];
    }

    /** @return BelongsTo<Shift, $this> */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /** @return HasMany<ClassSchedule, $this> */
    public function classSchedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function isClass(): bool
    {
        return $this->type === TimeSlotType::Class_;
    }

    public function isBreak(): bool
    {
        return $this->type === TimeSlotType::Break;
    }
}
