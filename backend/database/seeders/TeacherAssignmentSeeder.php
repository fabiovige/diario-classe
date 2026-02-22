<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeacherAssignmentSeeder extends Seeder
{
    private const ENROLLMENT_START = '2025-02-10';

    private const BATCH_SIZE = 500;

    public function run(): void
    {
        $components = DB::table('curricular_components')->where('active', true)->get();
        $experienceFields = DB::table('experience_fields')->where('active', true)->get();
        $schools = DB::table('schools')->orderBy('id')->pluck('id');
        $now = now()->toDateTimeString();

        $batch = [];
        $total = 0;

        foreach ($schools as $schoolId) {
            $academicYear = DB::table('academic_years')
                ->where('school_id', $schoolId)
                ->where('year', 2025)
                ->first();

            if (! $academicYear) {
                continue;
            }

            $classGroups = DB::table('class_groups')
                ->join('grade_levels', 'grade_levels.id', '=', 'class_groups.grade_level_id')
                ->where('class_groups.academic_year_id', $academicYear->id)
                ->select('class_groups.id', 'class_groups.grade_level_id', 'grade_levels.type')
                ->get();

            if ($classGroups->isEmpty()) {
                continue;
            }

            $teachers = DB::table('teachers')
                ->where('school_id', $schoolId)
                ->where('active', true)
                ->pluck('id')
                ->toArray();

            if (empty($teachers)) {
                continue;
            }

            $teacherCount = count($teachers);
            $polivalentIndex = 0;

            $specialistMap = [];
            foreach ($components as $i => $component) {
                $specialistMap[$component->id] = $teachers[$i % $teacherCount];
            }

            foreach ($classGroups as $cg) {
                if ($cg->type === 'early_childhood') {
                    $teacherId = $teachers[$polivalentIndex % $teacherCount];
                    $polivalentIndex++;

                    foreach ($experienceFields as $field) {
                        $batch[] = [
                            'class_group_id' => $cg->id,
                            'teacher_id' => $teacherId,
                            'curricular_component_id' => null,
                            'experience_field_id' => $field->id,
                            'start_date' => self::ENROLLMENT_START,
                            'active' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if ($cg->type === 'elementary_early') {
                    $teacherId = $teachers[$polivalentIndex % $teacherCount];
                    $polivalentIndex++;

                    foreach ($components as $component) {
                        $batch[] = [
                            'class_group_id' => $cg->id,
                            'teacher_id' => $teacherId,
                            'curricular_component_id' => $component->id,
                            'experience_field_id' => null,
                            'start_date' => self::ENROLLMENT_START,
                            'active' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if ($cg->type === 'elementary_late') {
                    foreach ($components as $component) {
                        $batch[] = [
                            'class_group_id' => $cg->id,
                            'teacher_id' => $specialistMap[$component->id],
                            'curricular_component_id' => $component->id,
                            'experience_field_id' => null,
                            'start_date' => self::ENROLLMENT_START,
                            'active' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if (count($batch) >= self::BATCH_SIZE) {
                    DB::table('teacher_assignments')->insert($batch);
                    $total += count($batch);
                    $batch = [];
                }
            }
        }

        if (! empty($batch)) {
            DB::table('teacher_assignments')->insert($batch);
            $total += count($batch);
        }

        $this->command->info("  TeacherAssignments: $total atribuicoes criadas.");
    }
}
