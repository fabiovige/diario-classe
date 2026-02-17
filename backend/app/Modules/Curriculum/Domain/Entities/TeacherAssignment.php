<?php

namespace App\Modules\Curriculum\Domain\Entities;

use App\Modules\People\Domain\Entities\Teacher;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 */
class TeacherAssignment extends Model
{
    /** @use HasFactory<\Database\Factories\TeacherAssignmentFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\TeacherAssignmentFactory
    {
        return \Database\Factories\TeacherAssignmentFactory::new();
    }

    protected $fillable = [
        'teacher_id',
        'class_group_id',
        'curricular_component_id',
        'experience_field_id',
        'start_date',
        'end_date',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Teacher, $this> */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /** @return BelongsTo<ClassGroup, $this> */
    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class);
    }

    /** @return BelongsTo<CurricularComponent, $this> */
    public function curricularComponent(): BelongsTo
    {
        return $this->belongsTo(CurricularComponent::class);
    }

    /** @return BelongsTo<ExperienceField, $this> */
    public function experienceField(): BelongsTo
    {
        return $this->belongsTo(ExperienceField::class);
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
