<?php

namespace App\Modules\Enrollment\Domain\Entities;

use App\Modules\Enrollment\Domain\Enums\DocumentStatus;
use App\Modules\Enrollment\Domain\Enums\DocumentType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property DocumentType $document_type
 * @property DocumentStatus $status
 * @property int|null $file_size
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 */
class EnrollmentDocument extends Model
{
    protected $fillable = [
        'enrollment_id',
        'document_type',
        'status',
        'notes',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'status' => DocumentStatus::class,
            'file_size' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Enrollment, $this> */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function hasFile(): bool
    {
        return $this->file_path !== null && $this->file_path !== '';
    }

    public function canUpload(): bool
    {
        return in_array($this->status, [DocumentStatus::NotUploaded, DocumentStatus::Rejected]);
    }

    public function canReview(): bool
    {
        return $this->status === DocumentStatus::PendingReview;
    }
}
