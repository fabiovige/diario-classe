<?php

namespace App\Modules\Attendance\Domain\Entities;

use App\Modules\People\Domain\Entities\Student;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property \Illuminate\Support\Carbon|null $approved_at
 */
class AbsenceJustification extends Model
{
    /** @use HasFactory<\Database\Factories\AbsenceJustificationFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\AbsenceJustificationFactory
    {
        return \Database\Factories\AbsenceJustificationFactory::new();
    }

    protected $fillable = [
        'student_id',
        'start_date',
        'end_date',
        'reason',
        'document_path',
        'approved',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'approved' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
