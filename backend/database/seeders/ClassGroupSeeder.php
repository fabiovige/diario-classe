<?php

namespace Database\Seeders;

use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\SchoolStructure\Domain\Entities\GradeLevel;
use App\Modules\SchoolStructure\Domain\Entities\School;
use App\Modules\SchoolStructure\Domain\Entities\Shift;
use App\Modules\SchoolStructure\Domain\Enums\GradeLevelType;
use Illuminate\Database\Seeder;

class ClassGroupSeeder extends Seeder
{
    private const MAX_STUDENTS_INFANTIL = 25;

    private const MAX_STUDENTS_FUNDAMENTAL = 30;

    private const GROUP_NAMES = ['A', 'B', 'C'];

    public function run(): void
    {
        $schools = School::orderBy('id')->get();
        $infantilGrades = GradeLevel::where('type', 'early_childhood')->orderBy('order')->get();
        $fundamentalGrades = GradeLevel::whereIn('type', ['elementary_early', 'elementary_late'])->orderBy('order')->get();

        foreach ($schools as $index => $school) {
            $academicYear = AcademicYear::where('school_id', $school->id)
                ->where('year', 2025)
                ->firstOrFail();

            $shifts = Shift::where('school_id', $school->id)
                ->whereIn('name', ['morning', 'afternoon'])
                ->get();

            $grades = $this->resolveGradesForSchool($index, $infantilGrades, $fundamentalGrades);

            foreach ($grades as $gradeLevel) {
                $maxStudents = $gradeLevel->type === GradeLevelType::EarlyChildhood
                    ? self::MAX_STUDENTS_INFANTIL
                    : self::MAX_STUDENTS_FUNDAMENTAL;

                $groupCount = $this->resolveGroupCount($gradeLevel);

                foreach ($shifts as $shift) {
                    for ($g = 0; $g < $groupCount; $g++) {
                        ClassGroup::updateOrCreate(
                            [
                                'academic_year_id' => $academicYear->id,
                                'grade_level_id' => $gradeLevel->id,
                                'shift_id' => $shift->id,
                                'name' => self::GROUP_NAMES[$g],
                            ],
                            ['max_students' => $maxStudents],
                        );
                    }
                }
            }
        }
    }

    /** @return \Illuminate\Database\Eloquent\Collection */
    private function resolveGradesForSchool(
        int $index,
        $infantilGrades,
        $fundamentalGrades,
    ) {
        if ($index < 10) {
            return $infantilGrades->merge($fundamentalGrades);
        }

        if ($index < 20) {
            return $fundamentalGrades;
        }

        return $infantilGrades;
    }

    private function resolveGroupCount(GradeLevel $gradeLevel): int
    {
        if ($gradeLevel->type === GradeLevelType::EarlyChildhood) {
            return 2;
        }

        return $gradeLevel->order <= 11 ? 3 : 2;
    }
}
