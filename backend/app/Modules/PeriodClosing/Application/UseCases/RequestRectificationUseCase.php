<?php

namespace App\Modules\PeriodClosing\Application\UseCases;

use App\Modules\PeriodClosing\Domain\Entities\PeriodClosing;
use App\Modules\PeriodClosing\Domain\Entities\Rectification;
use App\Modules\PeriodClosing\Domain\Enums\ClosingStatus;
use Illuminate\Validation\ValidationException;

final class RequestRectificationUseCase
{
    public function execute(
        int $periodClosingId,
        string $entityType,
        int $entityId,
        string $fieldChanged,
        string $oldValue,
        string $newValue,
        string $justification,
    ): Rectification {
        $closing = PeriodClosing::findOrFail($periodClosingId);

        if ($closing->status !== ClosingStatus::Closed) {
            throw ValidationException::withMessages([
                'status' => 'Retificações só podem ser solicitadas em períodos fechados.',
            ]);
        }

        return Rectification::create([
            'period_closing_id' => $periodClosingId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'field_changed' => $fieldChanged,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'justification' => $justification,
            'requested_by' => auth()->id(),
            'status' => 'requested',
        ]);
    }
}
