<?php

namespace App\Modules\PeriodClosing\Application\UseCases;

use App\Modules\PeriodClosing\Domain\Entities\PeriodClosing;
use App\Modules\PeriodClosing\Domain\Enums\ClosingStatus;
use Illuminate\Validation\ValidationException;

final class ValidatePeriodClosingUseCase
{
    public function execute(int $periodClosingId, bool $approve, ?string $rejectionReason = null): PeriodClosing
    {
        $closing = PeriodClosing::findOrFail($periodClosingId);

        if ($closing->status !== ClosingStatus::InValidation) {
            throw ValidationException::withMessages([
                'status' => 'O fechamento precisa estar em validação.',
            ]);
        }

        if ($approve) {
            $closing->update([
                'status' => ClosingStatus::Approved,
                'validated_by' => auth()->id(),
                'validated_at' => now(),
                'rejection_reason' => null,
            ]);

            return $closing->refresh();
        }

        $closing->update([
            'status' => ClosingStatus::Pending,
            'rejection_reason' => $rejectionReason,
        ]);

        return $closing->refresh();
    }
}
