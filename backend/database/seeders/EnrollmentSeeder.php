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

    private const MAX_STUDENTS_PER_SCHOOL = 100;

    public function run(): void
    {
        $faker = FakerFactory::create('pt_BR');
        $students = Student::all()->shuffle();
        $schools = School::orderBy('id')->get();

        $schoolClassGroups = $this->loadSchoolClassGroups($schools);
        $adminUser = \App\Models\User::where('email', 'admin@jandira.sp.gov.br')->first();
        $createdBy = $adminUser?->id;

        $studentIndex = 0;
        $enrollmentNumber = 1;
        $totalStudents = $students->count();

        foreach ($schoolClassGroups as $schoolData) {
            $schoolCount = 0;

            foreach ($schoolData['classGroups'] as $classGroup) {
                if ($studentIndex >= $totalStudents || $schoolCount >= self::MAX_STUDENTS_PER_SCHOOL) {
                    break;
                }

                $spotsToFill = min(
                    (int) ceil($classGroup->max_students * 0.8),
                    $totalStudents - $studentIndex,
                    self::MAX_STUDENTS_PER_SCHOOL - $schoolCount,
                );

                if ($spotsToFill <= 0) {
                    break;
                }

                for ($s = 0; $s < $spotsToFill; $s++) {
                    $student = $students[$studentIndex];
                    $studentIndex++;
                    $schoolCount++;

                    $enrollment = Enrollment::create([
                        'student_id' => $student->id,
                        'academic_year_id' => $schoolData['academicYear']->id,
                        'school_id' => $schoolData['school']->id,
                        'enrollment_number' => sprintf('MAT%06d', $enrollmentNumber),
                        'enrollment_type' => EnrollmentType::NewEnrollment->value,
                        'status' => 'active',
                        'enrollment_date' => self::ENROLLMENT_DATE,
                    ]);

                    $enrollmentNumber++;

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

            if ($studentIndex >= $totalStudents) {
                break;
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
