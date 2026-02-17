<?php

namespace App\Modules\Assessment\Domain\Entities;

use App\Modules\Assessment\Domain\Enums\FinalAverageStatus;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\People\Domain\Entities\Student;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalAverage extends Model
{
    protected $fillable = [
        'student_id',
        'class_group_id',
        'teacher_assignment_id',
        'academic_year_id',
        'numeric_average',
        'recovery_grade',
        'final_grade',
        'total_absences',
        'frequency_percentage',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'numeric_average' => 'decimal:2',
            'recovery_grade' => 'decimal:2',
            'final_grade' => 'decimal:2',
            'total_absences' => 'integer',
            'frequency_percentage' => 'decimal:2',
            'status' => FinalAverageStatus::class,
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

    /** @return BelongsTo<AcademicYear, $this> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
