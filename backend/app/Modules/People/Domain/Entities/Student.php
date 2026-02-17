<?php

namespace App\Modules\People\Domain\Entities;

use App\Modules\People\Domain\Enums\Gender;
use App\Modules\People\Domain\Enums\RaceColor;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property \Illuminate\Support\Carbon|null $birth_date
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

    public function displayName(): string
    {
        return $this->social_name ?? $this->name;
    }
}
