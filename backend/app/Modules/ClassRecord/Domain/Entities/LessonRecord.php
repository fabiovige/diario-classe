<?php

namespace App\Modules\ClassRecord\Domain\Entities;

use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property \Illuminate\Support\Carbon|null $date
 */
class LessonRecord extends Model
{
    /** @use HasFactory<\Database\Factories\LessonRecordFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\LessonRecordFactory
    {
        return \Database\Factories\LessonRecordFactory::new();
    }

    protected $fillable = [
        'class_group_id',
        'teacher_assignment_id',
        'date',
        'content',
        'methodology',
        'observations',
        'class_count',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'class_count' => 'integer',
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
}
