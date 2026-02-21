<?php

namespace App\Modules\SchoolStructure\Domain\Entities;

use App\Modules\SchoolStructure\Domain\Enums\ShiftName;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ShiftName $name
 * @property \Illuminate\Support\Carbon|null $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 */
class Shift extends Model
{
    /** @use HasFactory<\Database\Factories\ShiftFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\ShiftFactory
    {
        return \Database\Factories\ShiftFactory::new();
    }

    protected $fillable = [
        'school_id',
        'name',
        'start_time',
        'end_time',
    ];

    protected function casts(): array
    {
        return [
            'name' => ShiftName::class,
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
        ];
    }

    /** @return BelongsTo<School, $this> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
