<?php

namespace App\Modules\Enrollment\Domain\Entities;

use App\Modules\Enrollment\Domain\Enums\MovementType;
use App\Modules\SchoolStructure\Domain\Entities\School;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property \Illuminate\Support\Carbon|null $movement_date
 */
class EnrollmentMovement extends Model
{
    use Auditable;

    protected $fillable = [
        'enrollment_id',
        'type',
        'movement_date',
        'reason',
        'origin_school_id',
        'destination_school_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => MovementType::class,
            'movement_date' => 'date',
        ];
    }

    /** @return BelongsTo<Enrollment, $this> */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /** @return BelongsTo<School, $this> */
    public function originSchool(): BelongsTo
    {
        return $this->belongsTo(School::class, 'origin_school_id');
    }

    /** @return BelongsTo<School, $this> */
    public function destinationSchool(): BelongsTo
    {
        return $this->belongsTo(School::class, 'destination_school_id');
    }

    /** @return BelongsTo<\App\Models\User, $this> */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
