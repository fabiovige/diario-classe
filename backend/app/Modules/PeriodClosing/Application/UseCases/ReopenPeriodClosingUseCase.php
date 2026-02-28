<?php

namespace App\Modules\PeriodClosing\Application\UseCases;

use App\Modules\PeriodClosing\Domain\Entities\PeriodClosing;
use App\Modules\PeriodClosing\Domain\Enums\ClosingStatus;
use Illuminate\Validation\ValidationException;

final class ReopenPeriodClosingUseCase
{
    public function execute(int $periodClosingId, string $reason): PeriodClosing
    {
        $closing = PeriodClosing::findOrFail($periodClosingId);

        if ($closing->status !== ClosingStatus::Closed) {
            throw ValidationException::withMessages([
                'status' => "Apenas fechamentos com status 'Fechado' podem ser reabertos. Status atual: '{$closing->status->label()}'.",
            ]);
        }

        $closing->update([
            'status' => ClosingStatus::Pending,
            'submitted_by' => null,
            'submitted_at' => null,
            'validated_by' => null,
            'validated_at' => null,
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => $reason,
        ]);

        return $closing->refresh();
    }
}
