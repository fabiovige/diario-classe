<?php

namespace App\Modules\PeriodClosing\Application\UseCases;

use App\Modules\PeriodClosing\Domain\Entities\PeriodClosing;
use App\Modules\PeriodClosing\Domain\Enums\ClosingStatus;
use Illuminate\Validation\ValidationException;

final class SubmitPeriodClosingUseCase
{
    public function execute(int $periodClosingId): PeriodClosing
    {
        $closing = PeriodClosing::findOrFail($periodClosingId);

        if (! $closing->status->canTransitionTo(ClosingStatus::InValidation)) {
            throw ValidationException::withMessages([
                'status' => "Não é possível submeter um fechamento no status '{$closing->status->label()}'.",
            ]);
        }

        if (! $closing->all_grades_complete || ! $closing->all_attendance_complete || ! $closing->all_lesson_records_complete) {
            throw ValidationException::withMessages([
                'completeness' => 'Verificação de completude deve passar antes da submissão.',
            ]);
        }

        $closing->update([
            'status' => ClosingStatus::InValidation,
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
        ]);

        return $closing->refresh();
    }
}
