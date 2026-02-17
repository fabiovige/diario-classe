<?php

namespace App\Modules\SchoolStructure\Application\UseCases;

use App\Modules\SchoolStructure\Application\DTOs\CreateAcademicYearDTO;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;

final class CreateAcademicYearUseCase
{
    public function execute(CreateAcademicYearDTO $dto): AcademicYear
    {
        return AcademicYear::create([
            'school_id' => $dto->schoolId,
            'year' => $dto->year,
            'status' => $dto->status,
            'start_date' => $dto->startDate,
            'end_date' => $dto->endDate,
        ]);
    }
}
