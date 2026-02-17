<?php

namespace App\Modules\People\Domain\Entities;

use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Guardian extends Model
{
    /** @use HasFactory<\Database\Factories\GuardianFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\GuardianFactory
    {
        return \Database\Factories\GuardianFactory::new();
    }

    protected $fillable = [
        'name',
        'cpf',
        'rg',
        'phone',
        'phone_secondary',
        'email',
        'address',
        'occupation',
        'user_id',
    ];

    /** @return BelongsToMany<Student, $this> */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_guardian')
            ->withPivot(['relationship', 'is_primary', 'can_pickup'])
            ->withTimestamps();
    }

    /** @return BelongsTo<\App\Models\User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
