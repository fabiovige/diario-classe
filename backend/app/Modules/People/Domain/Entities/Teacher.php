<?php

namespace App\Modules\People\Domain\Entities;

use App\Modules\SchoolStructure\Domain\Entities\School;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property \Illuminate\Support\Carbon|null $hire_date
 */
class Teacher extends Model
{
    /** @use HasFactory<\Database\Factories\TeacherFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\TeacherFactory
    {
        return \Database\Factories\TeacherFactory::new();
    }

    protected $fillable = [
        'user_id',
        'school_id',
        'registration_number',
        'specialization',
        'hire_date',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'active' => 'boolean',
        ];
    }

    /** @return BelongsTo<\App\Models\User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /** @return BelongsTo<School, $this> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
