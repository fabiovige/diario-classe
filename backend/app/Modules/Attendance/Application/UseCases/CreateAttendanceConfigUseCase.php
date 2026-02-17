<?php

namespace App\Modules\Attendance\Application\UseCases;

use App\Modules\Attendance\Application\DTOs\CreateAttendanceConfigDTO;
use App\Modules\Attendance\Domain\Entities\AttendanceConfig;

final class CreateAttendanceConfigUseCase
{
    public function execute(CreateAttendanceConfigDTO $dto): AttendanceConfig
    {
        return AttendanceConfig::updateOrCreate(
            [
                'school_id' => $dto->schoolId,
                'academic_year_id' => $dto->academicYearId,
            ],
            [
                'consecutive_absences_alert' => $dto->consecutiveAbsencesAlert,
                'monthly_absences_alert' => $dto->monthlyAbsencesAlert,
                'period_absence_percentage_alert' => $dto->periodAbsencePercentageAlert,
                'annual_minimum_frequency' => $dto->annualMinimumFrequency,
            ],
        );
    }
}
