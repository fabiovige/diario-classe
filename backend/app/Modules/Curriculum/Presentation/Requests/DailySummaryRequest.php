<?php

namespace App\Modules\Curriculum\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DailySummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'teacher_id' => ['sometimes', 'integer', 'exists:teachers,id'],
        ];
    }
}
