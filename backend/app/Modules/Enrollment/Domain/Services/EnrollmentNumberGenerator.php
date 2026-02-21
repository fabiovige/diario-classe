<?php

namespace App\Modules\Enrollment\Domain\Services;

use App\Modules\Enrollment\Domain\Entities\Enrollment;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use Illuminate\Support\Facades\DB;

final class EnrollmentNumberGenerator
{
    public function generate(int $schoolId, int $academicYearId): string
    {
        $year = AcademicYear::where('id', $academicYearId)->value('year');

        $prefix = sprintf('%d-%s-', $year, str_pad((string) $schoolId, 3, '0', STR_PAD_LEFT));
        $prefixLength = strlen($prefix);

        $lastSequence = Enrollment::where('enrollment_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->max(DB::raw("CAST(SUBSTRING(enrollment_number, " . ($prefixLength + 1) . ") AS UNSIGNED)"));

        $nextSequence = ($lastSequence ?? 0) + 1;

        return $prefix . str_pad((string) $nextSequence, 5, '0', STR_PAD_LEFT);
    }
}
