<?php

namespace App\Modules\SchoolStructure\Domain\Entities;

use App\Modules\SchoolStructure\Domain\Enums\SchoolType;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    /** @use HasFactory<\Database\Factories\SchoolFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\SchoolFactory
    {
        return \Database\Factories\SchoolFactory::new();
    }

    protected $fillable = [
        'name',
        'inep_code',
        'type',
        'address',
        'phone',
        'email',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'type' => SchoolType::class,
            'active' => 'boolean',
        ];
    }

    /** @return HasMany<AcademicYear, $this> */
    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class);
    }

    /** @return HasMany<Shift, $this> */
    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    /** @return HasMany<\App\Models\User, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(\App\Models\User::class);
    }
}
