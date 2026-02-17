<?php

namespace App\Modules\Assessment\Domain\Entities;

use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\Assessment\Domain\Enums\RecoveryType;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\People\Domain\Entities\Student;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $numeric_value
 * @property string|null $conceptual_value
 * @property bool $is_recovery
 * @property RecoveryType|null $recovery_type
 */
class Grade extends Model
{
    /** @use HasFactory<\Database\Factories\GradeFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\GradeFactory
    {
        return \Database\Factories\GradeFactory::new();
    }

    protected $fillable = [
        'student_id',
        'class_group_id',
        'teacher_assignment_id',
        'assessment_period_id',
        'assessment_instrument_id',
        'numeric_value',
        'conceptual_value',
        'observations',
        'is_recovery',
        'recovery_type',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'numeric_value' => 'decimal:2',
            'is_recovery' => 'boolean',
            'recovery_type' => RecoveryType::class,
        ];
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
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

    /** @return BelongsTo<AssessmentInstrument, $this> */
    public function assessmentInstrument(): BelongsTo
    {
        return $this->belongsTo(AssessmentInstrument::class);
    }
}
