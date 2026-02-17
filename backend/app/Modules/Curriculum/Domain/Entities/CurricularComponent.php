<?php

namespace App\Modules\Curriculum\Domain\Entities;

use App\Modules\Curriculum\Domain\Enums\KnowledgeArea;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurricularComponent extends Model
{
    /** @use HasFactory<\Database\Factories\CurricularComponentFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\CurricularComponentFactory
    {
        return \Database\Factories\CurricularComponentFactory::new();
    }

    protected $fillable = [
        'name',
        'knowledge_area',
        'code',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'knowledge_area' => KnowledgeArea::class,
            'active' => 'boolean',
        ];
    }
}
