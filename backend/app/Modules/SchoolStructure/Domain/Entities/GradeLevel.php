<?php

namespace App\Modules\SchoolStructure\Domain\Entities;

use App\Modules\SchoolStructure\Domain\Enums\GradeLevelType;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    /** @use HasFactory<\Database\Factories\GradeLevelFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\GradeLevelFactory
    {
        return \Database\Factories\GradeLevelFactory::new();
    }

    protected $fillable = [
        'name',
        'type',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'type' => GradeLevelType::class,
            'order' => 'integer',
        ];
    }
}
