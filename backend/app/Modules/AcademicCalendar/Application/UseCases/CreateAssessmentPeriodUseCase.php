<?php

namespace App\Modules\AcademicCalendar\Application\UseCases;

use App\Modules\AcademicCalendar\Application\DTOs\CreateAssessmentPeriodDTO;
use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\AcademicCalendar\Domain\Enums\AssessmentPeriodType;
use Illuminate\Validation\ValidationException;

final class CreateAssessmentPeriodUseCase
{
    public function execute(CreateAssessmentPeriodDTO $dto): AssessmentPeriod
    {
        $type = AssessmentPeriodType::from($dto->type);

        if ($dto->number < 1 || $dto->number > $type->maxPeriods()) {
            throw ValidationException::withMessages([
                'number' => "O número do período deve ser entre 1 e {$type->maxPeriods()} para o tipo {$type->label()}.",
            ]);
        }

        return AssessmentPeriod::create([
            'academic_year_id' => $dto->academicYearId,
            'type' => $dto->type,
            'number' => $dto->number,
            'name' => $dto->name,
            'start_date' => $dto->startDate,
            'end_date' => $dto->endDate,
            'status' => 'open',
        ]);
    }
}
