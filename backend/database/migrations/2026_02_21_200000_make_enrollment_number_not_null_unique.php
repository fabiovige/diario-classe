<?php

use App\Modules\Enrollment\Domain\Entities\Enrollment;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->backfillEnrollmentNumbers();

        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('enrollment_number', 50)->nullable(false)->change();
            $table->unique('enrollment_number', 'enrollments_enrollment_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropUnique('enrollments_enrollment_number_unique');
            $table->string('enrollment_number', 50)->nullable()->change();
        });
    }

    private function backfillEnrollmentNumbers(): void
    {
        $enrollments = Enrollment::whereNull('enrollment_number')
            ->orWhere('enrollment_number', 'like', 'MAT%')
            ->orderBy('id')
            ->get();

        if ($enrollments->isEmpty()) {
            return;
        }

        $yearCache = [];
        $counters = [];

        foreach ($enrollments as $enrollment) {
            $key = $enrollment->academic_year_id . '-' . $enrollment->school_id;

            if (! isset($yearCache[$enrollment->academic_year_id])) {
                $yearCache[$enrollment->academic_year_id] = AcademicYear::where('id', $enrollment->academic_year_id)->value('year');
            }

            $year = $yearCache[$enrollment->academic_year_id];

            if (! isset($counters[$key])) {
                $prefix = sprintf('%d-%s-', $year, str_pad((string) $enrollment->school_id, 3, '0', STR_PAD_LEFT));
                $existing = Enrollment::where('enrollment_number', 'like', $prefix . '%')
                    ->where('enrollment_number', 'not like', 'MAT%')
                    ->count();
                $counters[$key] = $existing;
            }

            $counters[$key]++;

            $number = sprintf(
                '%d-%s-%s',
                $year,
                str_pad((string) $enrollment->school_id, 3, '0', STR_PAD_LEFT),
                str_pad((string) $counters[$key], 5, '0', STR_PAD_LEFT),
            );

            $enrollment->update(['enrollment_number' => $number]);
        }
    }
};
