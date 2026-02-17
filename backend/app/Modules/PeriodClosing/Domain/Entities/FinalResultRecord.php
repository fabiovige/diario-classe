<?php

namespace App\Modules\PeriodClosing\Domain\Entities;

use App\Modules\People\Domain\Entities\Student;
use App\Modules\PeriodClosing\Domain\Enums\FinalResult;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalResultRecord extends Model
{
    use Auditable;

    protected $table = 'final_results';

    protected $fillable = [
        'student_id',
        'class_group_id',
        'academic_year_id',
        'result',
        'overall_average',
        'overall_frequency',
        'council_override',
        'observations',
        'determined_by',
    ];

    protected function casts(): array
    {
        return [
            'result' => FinalResult::class,
            'overall_average' => 'decimal:2',
            'overall_frequency' => 'decimal:2',
            'council_override' => 'boolean',
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

    /** @return BelongsTo<AcademicYear, $this> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
