<?php

namespace App\Modules\PeriodClosing\Domain\Entities;

use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\PeriodClosing\Domain\Enums\ClosingStatus;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property ClosingStatus $status
 * @property \Illuminate\Support\Carbon|null $submitted_at
 * @property \Illuminate\Support\Carbon|null $validated_at
 * @property \Illuminate\Support\Carbon|null $approved_at
 */
class PeriodClosing extends Model
{
    /** @use HasFactory<\Database\Factories\PeriodClosingFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\PeriodClosingFactory
    {
        return \Database\Factories\PeriodClosingFactory::new();
    }

    protected $fillable = [
        'class_group_id',
        'teacher_assignment_id',
        'assessment_period_id',
        'status',
        'submitted_by',
        'submitted_at',
        'validated_by',
        'validated_at',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'all_grades_complete',
        'all_attendance_complete',
        'all_lesson_records_complete',
    ];

    protected function casts(): array
    {
        return [
            'status' => ClosingStatus::class,
            'submitted_at' => 'datetime',
            'validated_at' => 'datetime',
            'approved_at' => 'datetime',
            'all_grades_complete' => 'boolean',
            'all_attendance_complete' => 'boolean',
            'all_lesson_records_complete' => 'boolean',
        ];
    }

    /** @return BelongsTo<ClassGroup, $this> */
    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class);
    }

    /** @return BelongsTo<TeacherAssignment, $this> */
    public function teacherAssignment(): BelongsTo
    {
        return $this->belongsTo(TeacherAssignment::class);
    }

    /** @return BelongsTo<AssessmentPeriod, $this> */
    public function assessmentPeriod(): BelongsTo
    {
        return $this->belongsTo(AssessmentPeriod::class);
    }

    /** @return HasMany<Rectification, $this> */
    public function rectifications(): HasMany
    {
        return $this->hasMany(Rectification::class);
    }

    public function isClosed(): bool
    {
        return $this->status === ClosingStatus::Closed;
    }
}
