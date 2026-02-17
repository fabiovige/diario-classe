<?php

namespace App\Modules\SchoolStructure\Presentation\Requests;

use App\Modules\SchoolStructure\Domain\Enums\AcademicYearStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAcademicYearRequest extends FormRequest
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
            'year' => ['required', 'integer', 'min:2020', 'max:2099'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['sometimes', 'string', Rule::enum(AcademicYearStatus::class)],
        ];
    }
}
