<?php

namespace App\Modules\Assessment\Domain\Entities;

use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\Curriculum\Domain\Entities\ExperienceField;
use App\Modules\People\Domain\Entities\Student;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DescriptiveReport extends Model
{
    use Auditable;

    protected $fillable = [
        'student_id',
        'class_group_id',
        'experience_field_id',
        'assessment_period_id',
        'content',
        'recorded_by',
    ];

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

    /** @return BelongsTo<ExperienceField, $this> */
    public function experienceField(): BelongsTo
    {
        return $this->belongsTo(ExperienceField::class);
    }

    /** @return BelongsTo<AssessmentPeriod, $this> */
    public function assessmentPeriod(): BelongsTo
    {
        return $this->belongsTo(AssessmentPeriod::class);
    }
}
