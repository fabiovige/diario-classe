<?php

namespace App\Modules\Assessment\Domain\Entities;

use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\People\Domain\Entities\Student;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property \Illuminate\Support\Carbon|null $calculated_at
 */
class PeriodAverage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'class_group_id',
        'teacher_assignment_id',
        'assessment_period_id',
        'numeric_average',
        'conceptual_average',
        'total_absences',
        'frequency_percentage',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'numeric_average' => 'decimal:2',
            'total_absences' => 'integer',
            'frequency_percentage' => 'decimal:2',
            'calculated_at' => 'datetime',
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
}
