<?php

namespace App\Modules\PeriodClosing\Application\UseCases;

use App\Modules\PeriodClosing\Domain\Entities\PeriodClosing;
use App\Modules\PeriodClosing\Domain\Enums\ClosingStatus;
use Illuminate\Validation\ValidationException;

final class ClosePeriodUseCase
{
    public function execute(int $periodClosingId): PeriodClosing
    {
        $closing = PeriodClosing::findOrFail($periodClosingId);

        if (! $closing->status->canTransitionTo(ClosingStatus::Closed)) {
            throw ValidationException::withMessages([
                'status' => "Não é possível fechar um fechamento no status '{$closing->status->label()}'.",
            ]);
        }

        $closing->update([
            'status' => ClosingStatus::Closed,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return $closing->refresh();
    }
}
