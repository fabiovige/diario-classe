<?php

namespace App\Modules\Curriculum\Domain\Entities;

use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperienceField extends Model
{
    /** @use HasFactory<\Database\Factories\ExperienceFieldFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\ExperienceFieldFactory
    {
        return \Database\Factories\ExperienceFieldFactory::new();
    }

    protected $fillable = [
        'name',
        'code',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
