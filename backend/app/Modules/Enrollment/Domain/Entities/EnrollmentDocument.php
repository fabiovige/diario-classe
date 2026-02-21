<?php

namespace App\Modules\Enrollment\Domain\Entities;

use App\Modules\Enrollment\Domain\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property DocumentType $document_type
 * @property \Illuminate\Support\Carbon|null $delivered_at
 */
class EnrollmentDocument extends Model
{
    protected $fillable = [
        'enrollment_id',
        'document_type',
        'delivered',
        'delivered_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'delivered' => 'boolean',
            'delivered_at' => 'date',
        ];
    }

    /** @return BelongsTo<Enrollment, $this> */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }
}
