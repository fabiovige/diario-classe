<?php

namespace App\Modules\Assessment\Domain\Entities;

use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConceptualScale extends Model
{
    use Auditable;

    protected $fillable = [
        'assessment_config_id',
        'code',
        'label',
        'numeric_equivalent',
        'passing',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'numeric_equivalent' => 'decimal:2',
            'passing' => 'boolean',
            'order' => 'integer',
        ];
    }

    /** @return BelongsTo<AssessmentConfig, $this> */
    public function assessmentConfig(): BelongsTo
    {
        return $this->belongsTo(AssessmentConfig::class);
    }
}
