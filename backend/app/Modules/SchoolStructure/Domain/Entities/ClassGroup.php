<?php

namespace App\Modules\SchoolStructure\Domain\Entities;

use App\Modules\Enrollment\Domain\Entities\ClassAssignment;
use App\Modules\Enrollment\Domain\Enums\ClassAssignmentStatus;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassGroup extends Model
{
    /** @use HasFactory<\Database\Factories\ClassGroupFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\ClassGroupFactory
    {
        return \Database\Factories\ClassGroupFactory::new();
    }

    protected $fillable = [
        'academic_year_id',
        'grade_level_id',
        'shift_id',
        'name',
        'max_students',
    ];

    protected function casts(): array
    {
        return [
            'max_students' => 'integer',
        ];
    }

    /** @return BelongsTo<AcademicYear, $this> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /** @return BelongsTo<GradeLevel, $this> */
    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    /** @return BelongsTo<Shift, $this> */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /** @return HasMany<ClassAssignment, $this> */
    public function classAssignments(): HasMany
    {
        return $this->hasMany(ClassAssignment::class);
    }

    /** @return HasMany<ClassAssignment, $this> */
    public function activeClassAssignments(): HasMany
    {
        return $this->hasMany(ClassAssignment::class)
            ->where('status', ClassAssignmentStatus::Active->value);
    }
}
