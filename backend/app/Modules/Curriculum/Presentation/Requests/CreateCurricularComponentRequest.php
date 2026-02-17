<?php

namespace App\Modules\Curriculum\Presentation\Requests;

use App\Modules\Curriculum\Domain\Enums\KnowledgeArea;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCurricularComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:curricular_components,name'],
            'knowledge_area' => ['required', 'string', Rule::in(array_column(KnowledgeArea::cases(), 'value'))],
            'code' => ['nullable', 'string', 'max:20'],
        ];
    }
}
