<?php

namespace App\Modules\People\Domain\Entities;

use App\Modules\People\Domain\Enums\DisabilityType;
use App\Modules\People\Domain\Enums\Gender;
use App\Modules\People\Domain\Enums\RaceColor;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use App\Modules\Enrollment\Domain\Entities\Enrollment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property DisabilityType|null $disability_type
 */
class Student extends Model
{
    /** @use HasFactory<\Database\Factories\StudentFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\StudentFactory
    {
        return \Database\Factories\StudentFactory::new();
    }

    protected $fillable = [
        'name',
        'social_name',
        'birth_date',
        'gender',
        'race_color',
        'cpf',
        'rg',
        'sus_number',
        'nis_number',
        'birth_city',
        'birth_state',
        'nationality',
        'medical_notes',
        'has_disability',
        'disability_type',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'gender' => Gender::class,
            'race_color' => RaceColor::class,
            'has_disability' => 'boolean',
            'disability_type' => DisabilityType::class,
            'active' => 'boolean',
        ];
    }

    /** @return BelongsToMany<Guardian, $this> */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'student_guardian')
            ->withPivot(['relationship', 'is_primary', 'can_pickup'])
            ->withTimestamps();
    }

    /** @return HasMany<Enrollment, $this> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function displayName(): string
    {
        return $this->social_name ?? $this->name;
    }
}
