<?php

namespace Database\Seeders;

use App\Modules\Enrollment\Domain\Entities\ClassAssignment;
use App\Modules\Enrollment\Domain\Entities\Enrollment;
use App\Modules\Enrollment\Domain\Entities\EnrollmentMovement;
use App\Modules\Enrollment\Domain\Enums\EnrollmentType;
use App\Modules\People\Domain\Entities\Student;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\SchoolStructure\Domain\Entities\School;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    private const ENROLLMENT_DATE = '2025-02-10';

    private const TRANSFER_PERCENTAGE = 5;

    private const FILL_RATE = 0.7;

    public function run(): void
    {
        $faker = FakerFactory::create('pt_BR');
        $schools = School::orderBy('id')->get();
        $adminUser = \App\Models\User::where('email', 'admin@jandira.sp.gov.br')->first();
        $createdBy = $adminUser?->id;

        $schoolClassGroups = $this->loadSchoolClassGroups($schools);
        $totalSpotsNeeded = $this->calculateTotalSpots($schoolClassGroups);

        $existingStudentCount = Student::count();
        $studentsToCreate = max(0, $totalSpotsNeeded - $existingStudentCount);

        if ($studentsToCreate > 0) {
            Student::factory()->count($studentsToCreate)->create();
        }

        $students = Student::all()->shuffle();
        $studentIndex = 0;

        foreach ($schoolClassGroups as $schoolData) {
            $schoolSequential = 0;
            $schoolId = $schoolData['school']->id;
            $year = $schoolData['academicYear']->year;

            foreach ($schoolData['classGroups'] as $classGroup) {
                if ($studentIndex >= $students->count()) {
                    break 2;
                }

                $spotsToFill = (int) ceil($classGroup->max_students * self::FILL_RATE);
                $spotsToFill = min($spotsToFill, $students->count() - $studentIndex);

                for ($s = 0; $s < $spotsToFill; $s++) {
                    $student = $students[$studentIndex];
                    $studentIndex++;
                    $schoolSequential++;

                    $enrollment = Enrollment::create([
                        'student_id' => $student->id,
                        'academic_year_id' => $schoolData['academicYear']->id,
                        'school_id' => $schoolId,
                        'enrollment_number' => sprintf('%d-%s-%s', $year, str_pad((string) $schoolId, 3, '0', STR_PAD_LEFT), str_pad((string) $schoolSequential, 5, '0', STR_PAD_LEFT)),
                        'enrollment_type' => EnrollmentType::NewEnrollment->value,
                        'status' => 'active',
                        'enrollment_date' => self::ENROLLMENT_DATE,
                    ]);

                    ClassAssignment::create([
                        'enrollment_id' => $enrollment->id,
                        'class_group_id' => $classGroup->id,
                        'status' => 'active',
                        'start_date' => self::ENROLLMENT_DATE,
                    ]);

                    EnrollmentMovement::create([
                        'enrollment_id' => $enrollment->id,
                        'type' => 'matricula_inicial',
                        'movement_date' => self::ENROLLMENT_DATE,
                        'reason' => 'Matrícula inicial ano letivo 2025',
                        'created_by' => $createdBy,
                    ]);

                    if (! $faker->boolean(self::TRANSFER_PERCENTAGE)) {
                        continue;
                    }

                    $this->applyTransfer($faker, $enrollment, $createdBy);
                }
            }
        }
    }

    /** @return array<int, array{school: School, academicYear: AcademicYear, classGroups: \Illuminate\Database\Eloquent\Collection}> */
    private function loadSchoolClassGroups($schools): array
    {
        $result = [];

        foreach ($schools as $school) {
            $academicYear = AcademicYear::where('school_id', $school->id)
                ->where('year', 2025)
                ->first();

            if (! $academicYear) {
                continue;
            }

            $classGroups = ClassGroup::where('academic_year_id', $academicYear->id)
                ->orderBy('grade_level_id')
                ->orderBy('shift_id')
                ->orderBy('name')
                ->get();

            if ($classGroups->isEmpty()) {
                continue;
            }

            $result[] = [
                'school' => $school,
                'academicYear' => $academicYear,
                'classGroups' => $classGroups,
            ];
        }

        return $result;
    }

    private function calculateTotalSpots(array $schoolClassGroups): int
    {
        $total = 0;
        foreach ($schoolClassGroups as $schoolData) {
            foreach ($schoolData['classGroups'] as $classGroup) {
                $total += (int) ceil($classGroup->max_students * self::FILL_RATE);
            }
        }

        return $total;
    }

    private function applyTransfer(
        \Faker\Generator $faker,
        Enrollment $enrollment,
        ?int $createdBy,
    ): void {
        $transferType = $faker->randomElement([
            'transferencia_interna',
            'transferencia_externa',
        ]);

        $transferDate = $faker->dateTimeBetween('2025-03-01', '2025-06-30')->format('Y-m-d');

        $enrollment->update([
            'status' => 'transferred',
            'exit_date' => $transferDate,
        ]);

        $assignment = $enrollment->classAssignments()->where('status', 'active')->first();
        if ($assignment) {
            $assignment->update([
                'status' => 'transferred',
                'end_date' => $transferDate,
            ]);
        }

        $reason = $transferType === 'transferencia_interna'
            ? 'Transferência para outra escola da rede municipal'
            : 'Transferência para escola de outro município';

        EnrollmentMovement::create([
            'enrollment_id' => $enrollment->id,
            'type' => $transferType,
            'movement_date' => $transferDate,
            'reason' => $reason,
            'created_by' => $createdBy,
        ]);
    }
}
