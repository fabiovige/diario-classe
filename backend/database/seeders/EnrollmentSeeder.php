<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnrollmentSeeder extends Seeder
{
    private const ENROLLMENT_DATE = '2025-02-10';

    private const TRANSFER_PERCENTAGE = 5;

    private const FILL_RATE = 0.7;

    private const BATCH_SIZE = 500;

    public function run(): void
    {
        $faker = \Faker\Factory::create('pt_BR');
        $adminId = DB::table('users')->where('email', 'admin@jandira.sp.gov.br')->value('id');

        $schools = DB::table('schools')->orderBy('id')->pluck('id');
        $now = now()->toDateTimeString();

        $schoolClassGroups = $this->loadSchoolClassGroups($schools);
        $totalSpotsNeeded = $this->calculateTotalSpots($schoolClassGroups);

        $existingStudentCount = DB::table('students')->count();
        $studentsToCreate = max(0, $totalSpotsNeeded - $existingStudentCount);

        if ($studentsToCreate > 0) {
            $this->command->info("  Enrollment: criando $studentsToCreate alunos extras...");
            $fakerPt = \Faker\Factory::create('pt_BR');
            $batch = [];
            for ($i = 0; $i < $studentsToCreate; $i++) {
                $gender = $fakerPt->randomElement(['male', 'female']);
                $firstName = $gender === 'male' ? $fakerPt->firstNameMale() : $fakerPt->firstNameFemale();
                $batch[] = [
                    'name' => "{$firstName} {$fakerPt->lastName()} {$fakerPt->lastName()}",
                    'birth_date' => $fakerPt->dateTimeBetween('-14 years', '-4 years')->format('Y-m-d'),
                    'gender' => $gender,
                    'race_color' => $fakerPt->randomElement(['parda', 'branca', 'preta']),
                    'cpf' => $fakerPt->unique()->cpf(false),
                    'sus_number' => $fakerPt->numerify('###############'),
                    'birth_city' => 'Jandira',
                    'birth_state' => 'SP',
                    'nationality' => 'brasileira',
                    'has_disability' => false,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($batch) >= self::BATCH_SIZE) {
                    DB::table('students')->insert($batch);
                    $batch = [];
                }
            }
            if (! empty($batch)) {
                DB::table('students')->insert($batch);
            }
        }

        $studentIds = DB::table('students')->pluck('id')->shuffle()->toArray();
        $studentIndex = 0;

        $enrollmentBatch = [];
        $assignmentBatch = [];
        $movementBatch = [];
        $transferCandidates = [];

        foreach ($schoolClassGroups as $schoolData) {
            $schoolSequential = 0;
            $schoolId = $schoolData['school_id'];
            $academicYearId = $schoolData['academic_year_id'];
            $year = $schoolData['year'];

            foreach ($schoolData['class_groups'] as $classGroup) {
                if ($studentIndex >= count($studentIds)) {
                    break 2;
                }

                $spotsToFill = (int) ceil($classGroup->max_students * self::FILL_RATE);
                $spotsToFill = min($spotsToFill, count($studentIds) - $studentIndex);

                for ($s = 0; $s < $spotsToFill; $s++) {
                    $studentId = $studentIds[$studentIndex];
                    $studentIndex++;
                    $schoolSequential++;

                    $enrollmentNumber = sprintf(
                        '%d-%s-%s',
                        $year,
                        str_pad((string) $schoolId, 3, '0', STR_PAD_LEFT),
                        str_pad((string) $schoolSequential, 5, '0', STR_PAD_LEFT),
                    );

                    $enrollmentBatch[] = [
                        'student_id' => $studentId,
                        'academic_year_id' => $academicYearId,
                        'school_id' => $schoolId,
                        'enrollment_number' => $enrollmentNumber,
                        'enrollment_type' => 'new_enrollment',
                        'status' => 'active',
                        'enrollment_date' => self::ENROLLMENT_DATE,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $assignmentBatch[] = [
                        'class_group_id' => $classGroup->id,
                        'status' => 'active',
                        'start_date' => self::ENROLLMENT_DATE,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $movementBatch[] = [
                        'type' => 'matricula_inicial',
                        'movement_date' => self::ENROLLMENT_DATE,
                        'reason' => 'Matrícula inicial ano letivo 2025',
                        'created_by' => $adminId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    if ($faker->boolean(self::TRANSFER_PERCENTAGE)) {
                        $transferCandidates[] = count($enrollmentBatch) - 1;
                    }
                }
            }
        }

        $this->command->info('  Enrollment: inserindo matriculas...');

        foreach (array_chunk($enrollmentBatch, self::BATCH_SIZE) as $chunkIndex => $chunk) {
            DB::table('enrollments')->insert($chunk);
        }

        $enrollmentIds = DB::table('enrollments')
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        $assignmentInserts = [];
        foreach ($assignmentBatch as $i => $assignment) {
            $assignmentInserts[] = array_merge($assignment, [
                'enrollment_id' => $enrollmentIds[$i],
            ]);
        }

        foreach (array_chunk($assignmentInserts, self::BATCH_SIZE) as $chunk) {
            DB::table('class_assignments')->insert($chunk);
        }

        $movementInserts = [];
        foreach ($movementBatch as $i => $movement) {
            $movementInserts[] = array_merge($movement, [
                'enrollment_id' => $enrollmentIds[$i],
            ]);
        }

        foreach (array_chunk($movementInserts, self::BATCH_SIZE) as $chunk) {
            DB::table('enrollment_movements')->insert($chunk);
        }

        $this->command->info('  Enrollment: processando transferencias...');
        $this->processTransfers($faker, $transferCandidates, $enrollmentIds, $adminId);
        $this->command->info('  Enrollment: ' . count($enrollmentBatch) . ' matriculas finalizadas.');
    }

    private function processTransfers(
        \Faker\Generator $faker,
        array $transferCandidates,
        array $enrollmentIds,
        ?int $adminId,
    ): void {
        foreach ($transferCandidates as $index) {
            $enrollmentId = $enrollmentIds[$index];
            $transferType = $faker->randomElement(['transferencia_interna', 'transferencia_externa']);
            $transferDate = $faker->dateTimeBetween('2025-03-01', '2025-04-17')->format('Y-m-d');

            DB::table('enrollments')
                ->where('id', $enrollmentId)
                ->update(['status' => 'transferred', 'exit_date' => $transferDate]);

            DB::table('class_assignments')
                ->where('enrollment_id', $enrollmentId)
                ->where('status', 'active')
                ->update(['status' => 'transferred', 'end_date' => $transferDate]);

            $reason = $transferType === 'transferencia_interna'
                ? 'Transferência para outra escola da rede municipal'
                : 'Transferência para escola de outro município';

            DB::table('enrollment_movements')->insert([
                'enrollment_id' => $enrollmentId,
                'type' => $transferType,
                'movement_date' => $transferDate,
                'reason' => $reason,
                'created_by' => $adminId,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ]);
        }
    }

    /**
     * @return array<int, array{school_id: int, academic_year_id: int, year: int, class_groups: \Illuminate\Support\Collection}>
     */
    private function loadSchoolClassGroups($schools): array
    {
        $result = [];

        foreach ($schools as $schoolId) {
            $academicYear = DB::table('academic_years')
                ->where('school_id', $schoolId)
                ->where('year', 2025)
                ->first();

            if (! $academicYear) {
                continue;
            }

            $classGroups = DB::table('class_groups')
                ->where('academic_year_id', $academicYear->id)
                ->orderBy('grade_level_id')
                ->orderBy('shift_id')
                ->orderBy('name')
                ->get();

            if ($classGroups->isEmpty()) {
                continue;
            }

            $result[] = [
                'school_id' => $schoolId,
                'academic_year_id' => $academicYear->id,
                'year' => $academicYear->year,
                'class_groups' => $classGroups,
            ];
        }

        return $result;
    }

    private function calculateTotalSpots(array $schoolClassGroups): int
    {
        $total = 0;
        foreach ($schoolClassGroups as $schoolData) {
            foreach ($schoolData['class_groups'] as $classGroup) {
                $total += (int) ceil($classGroup->max_students * self::FILL_RATE);
            }
        }

        return $total;
    }
}
