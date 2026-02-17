<?php

namespace App\Modules\AcademicCalendar\Domain\Entities;

use App\Modules\AcademicCalendar\Domain\Enums\AssessmentPeriodStatus;
use App\Modules\AcademicCalendar\Domain\Enums\AssessmentPeriodType;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property AssessmentPeriodType $type
 * @property AssessmentPeriodStatus $status
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 */
class AssessmentPeriod extends Model
{
    /** @use HasFactory<\Database\Factories\AssessmentPeriodFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\AssessmentPeriodFactory
    {
        return \Database\Factories\AssessmentPeriodFactory::new();
    }

    protected $fillable = [
        'academic_year_id',
        'type',
        'number',
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => AssessmentPeriodType::class,
            'number' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => AssessmentPeriodStatus::class,
        ];
    }

    /** @return BelongsTo<AcademicYear, $this> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function isOpen(): bool
    {
        return $this->status === AssessmentPeriodStatus::Open;
    }

    public function isClosed(): bool
    {
        return $this->status === AssessmentPeriodStatus::Closed;
    }
}
