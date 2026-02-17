<?php

namespace App\Modules\Enrollment\Domain\Entities;

use App\Modules\Enrollment\Domain\Enums\EnrollmentStatus;
use App\Modules\People\Domain\Entities\Student;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Entities\School;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property \App\Modules\Enrollment\Domain\Enums\EnrollmentStatus $status
 * @property \Illuminate\Support\Carbon|null $enrollment_date
 * @property \Illuminate\Support\Carbon|null $exit_date
 */
class Enrollment extends Model
{
    /** @use HasFactory<\Database\Factories\EnrollmentFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\EnrollmentFactory
    {
        return \Database\Factories\EnrollmentFactory::new();
    }

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'school_id',
        'enrollment_number',
        'status',
        'enrollment_date',
        'exit_date',
    ];

    protected function casts(): array
    {
        return [
            'status' => EnrollmentStatus::class,
            'enrollment_date' => 'date',
            'exit_date' => 'date',
        ];
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<AcademicYear, $this> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /** @return BelongsTo<School, $this> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** @return HasMany<ClassAssignment, $this> */
    public function classAssignments(): HasMany
    {
        return $this->hasMany(ClassAssignment::class);
    }

    /** @return HasMany<EnrollmentMovement, $this> */
    public function movements(): HasMany
    {
        return $this->hasMany(EnrollmentMovement::class);
    }

    public function isActive(): bool
    {
        return $this->status === EnrollmentStatus::Active;
    }
}
