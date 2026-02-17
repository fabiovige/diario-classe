<?php

namespace App\Modules\Identity\Domain\Entities;

use App\Modules\Identity\Domain\Enums\RoleSlug;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property RoleSlug $slug
 */
class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\RoleFactory
    {
        return \Database\Factories\RoleFactory::new();
    }

    protected $attributes = [
        'permissions' => '[]',
    ];

    protected $fillable = [
        'name',
        'slug',
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'slug' => RoleSlug::class,
            'permissions' => 'array',
        ];
    }

    /** @return HasMany<\App\Models\User, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(\App\Models\User::class);
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? [], true);
    }
}
