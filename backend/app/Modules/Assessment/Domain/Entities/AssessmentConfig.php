<?php

namespace App\Modules\Assessment\Domain\Entities;

use App\Modules\Assessment\Domain\Enums\AverageFormula;
use App\Modules\Assessment\Domain\Enums\GradeType;
use App\Modules\Assessment\Domain\Enums\RecoveryReplaces;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Entities\GradeLevel;
use App\Modules\SchoolStructure\Domain\Entities\School;
use App\Modules\Shared\Audit\Infrastructure\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property GradeType $grade_type
 * @property AverageFormula $average_formula
 * @property RecoveryReplaces $recovery_replaces
 * @property int $rounding_precision
 * @property bool $recovery_enabled
 */
class AssessmentConfig extends Model
{
    /** @use HasFactory<\Database\Factories\AssessmentConfigFactory> */
    use Auditable, HasFactory;

    protected static function newFactory(): \Database\Factories\AssessmentConfigFactory
    {
        return \Database\Factories\AssessmentConfigFactory::new();
    }

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'grade_level_id',
        'grade_type',
        'scale_min',
        'scale_max',
        'passing_grade',
        'average_formula',
        'rounding_precision',
        'recovery_enabled',
        'recovery_replaces',
    ];

    protected function casts(): array
    {
        return [
            'grade_type' => GradeType::class,
            'scale_min' => 'decimal:2',
            'scale_max' => 'decimal:2',
            'passing_grade' => 'decimal:2',
            'average_formula' => AverageFormula::class,
            'rounding_precision' => 'integer',
            'recovery_enabled' => 'boolean',
            'recovery_replaces' => RecoveryReplaces::class,
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

    /** @return BelongsTo<GradeLevel, $this> */
    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    /** @return HasMany<ConceptualScale, $this> */
    public function conceptualScales(): HasMany
    {
        return $this->hasMany(ConceptualScale::class);
    }

    /** @return HasMany<AssessmentInstrument, $this> */
    public function instruments(): HasMany
    {
        return $this->hasMany(AssessmentInstrument::class);
    }
}
