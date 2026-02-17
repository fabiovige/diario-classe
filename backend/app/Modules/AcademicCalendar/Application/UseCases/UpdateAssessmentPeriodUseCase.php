<?php

namespace App\Modules\AcademicCalendar\Application\UseCases;

use App\Modules\AcademicCalendar\Application\DTOs\UpdateAssessmentPeriodDTO;
use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\AcademicCalendar\Domain\Enums\AssessmentPeriodStatus;
use Illuminate\Validation\ValidationException;

final class UpdateAssessmentPeriodUseCase
{
    public function execute(UpdateAssessmentPeriodDTO $dto): AssessmentPeriod
    {
        $period = AssessmentPeriod::findOrFail($dto->id);

        if ($period->isClosed()) {
            throw ValidationException::withMessages([
                'status' => 'Não é possível alterar um período já fechado.',
            ]);
        }

        $data = array_filter([
            'name' => $dto->name,
            'start_date' => $dto->startDate,
            'end_date' => $dto->endDate,
            'status' => $dto->status,
        ], fn ($value) => $value !== null);

        if (isset($data['status'])) {
            $this->validateStatusTransition($period->status, AssessmentPeriodStatus::from($data['status']));
        }

        $period->update($data);

        return $period->refresh();
    }

    private function validateStatusTransition(AssessmentPeriodStatus $current, AssessmentPeriodStatus $new): void
    {
        $allowed = match ($current) {
            AssessmentPeriodStatus::Open => [AssessmentPeriodStatus::Closing],
            AssessmentPeriodStatus::Closing => [AssessmentPeriodStatus::Open, AssessmentPeriodStatus::Closed],
            AssessmentPeriodStatus::Closed => [],
        };

        if (! in_array($new, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => "Transição de '{$current->label()}' para '{$new->label()}' não é permitida.",
            ]);
        }
    }
}
