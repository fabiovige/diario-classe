<?php

namespace App\Modules\Assessment\Presentation\Requests;

use App\Modules\Assessment\Domain\Enums\AverageFormula;
use App\Modules\Assessment\Domain\Enums\GradeType;
use App\Modules\Assessment\Domain\Enums\RecoveryReplaces;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAssessmentConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'grade_type' => ['required', 'string', Rule::in(array_column(GradeType::cases(), 'value'))],
            'scale_min' => ['nullable', 'numeric', 'min:0'],
            'scale_max' => ['nullable', 'numeric', 'min:0'],
            'passing_grade' => ['nullable', 'numeric', 'min:0'],
            'average_formula' => ['sometimes', 'string', Rule::in(array_column(AverageFormula::cases(), 'value'))],
            'rounding_precision' => ['sometimes', 'integer', 'min:0', 'max:4'],
            'recovery_enabled' => ['sometimes', 'boolean'],
            'recovery_replaces' => ['sometimes', 'string', Rule::in(array_column(RecoveryReplaces::cases(), 'value'))],
        ];
    }
}
