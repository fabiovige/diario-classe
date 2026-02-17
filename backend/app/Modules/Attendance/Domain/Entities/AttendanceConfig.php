<?php

namespace App\Modules\Attendance\Domain\Entities;

use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Entities\School;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceConfig extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceConfigFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\AttendanceConfigFactory
    {
        return \Database\Factories\AttendanceConfigFactory::new();
    }

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'consecutive_absences_alert',
        'monthly_absences_alert',
        'period_absence_percentage_alert',
        'annual_minimum_frequency',
    ];

    protected function casts(): array
    {
        return [
            'consecutive_absences_alert' => 'integer',
            'monthly_absences_alert' => 'integer',
            'period_absence_percentage_alert' => 'decimal:2',
            'annual_minimum_frequency' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<School, $this> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** @return BelongsTo<AcademicYear, $this> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
