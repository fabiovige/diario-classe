<?php

namespace App\Modules\PeriodClosing\Application\UseCases;

use App\Modules\PeriodClosing\Domain\Entities\PeriodClosing;
use App\Modules\PeriodClosing\Domain\Enums\ClosingStatus;
use Illuminate\Validation\ValidationException;

final class TeacherClosePeriodUseCase
{
    public function __construct(
        private RunCompletenessCheckUseCase $completenessCheck,
    ) {}

    public function execute(int $periodClosingId): PeriodClosing
    {
        $closing = PeriodClosing::findOrFail($periodClosingId);

        if ($closing->status !== ClosingStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => "Apenas fechamentos pendentes podem ser fechados diretamente. Status atual: '{$closing->status->label()}'.",
            ]);
        }

        $closing = $this->completenessCheck->execute($periodClosingId);

        $pendingItems = $this->getPendingItems($closing);

        if (count($pendingItems) > 0) {
            throw ValidationException::withMessages([
                'completeness' => 'Itens pendentes: ' . implode(', ', $pendingItems) . '.',
            ]);
        }

        $closing->update([
            'status' => ClosingStatus::Closed,
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return $closing->refresh();
    }

    /** @return array<string> */
    private function getPendingItems(PeriodClosing $closing): array
    {
        $items = [];

        if (! $closing->all_grades_complete) {
            $items[] = 'notas';
        }

        if (! $closing->all_attendance_complete) {
            $items[] = 'frequencia';
        }

        if (! $closing->all_lesson_records_complete) {
            $items[] = 'diario de classe';
        }

        return $items;
    }
}
