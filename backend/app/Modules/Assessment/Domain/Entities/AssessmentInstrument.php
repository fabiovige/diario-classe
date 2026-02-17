<?php

namespace App\Modules\Assessment\Domain\Entities;

use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentInstrument extends Model
{
    /** @use HasFactory<\Database\Factories\AssessmentInstrumentFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\AssessmentInstrumentFactory
    {
        return \Database\Factories\AssessmentInstrumentFactory::new();
    }

    protected $fillable = [
        'assessment_config_id',
        'name',
        'weight',
        'max_value',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'max_value' => 'decimal:2',
            'order' => 'integer',
        ];
    }

    /** @return BelongsTo<AssessmentConfig, $this> */
    public function assessmentConfig(): BelongsTo
    {
        return $this->belongsTo(AssessmentConfig::class);
    }
}
