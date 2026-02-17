<?php

namespace App\Modules\PeriodClosing\Domain\Entities;

use App\Modules\PeriodClosing\Domain\Enums\RectificationStatus;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rectification extends Model
{
    use Auditable;

    protected $fillable = [
        'period_closing_id',
        'entity_type',
        'entity_id',
        'field_changed',
        'old_value',
        'new_value',
        'justification',
        'requested_by',
        'approved_by',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => RectificationStatus::class,
        ];
    }

    /** @return BelongsTo<PeriodClosing, $this> */
    public function periodClosing(): BelongsTo
    {
        return $this->belongsTo(PeriodClosing::class);
    }
}
