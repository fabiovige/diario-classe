<?php

namespace Database\Seeders;

use App\Modules\Curriculum\Domain\Entities\CurricularComponent;
use App\Modules\Curriculum\Domain\Entities\ExperienceField;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\People\Domain\Entities\Teacher;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\SchoolStructure\Domain\Enums\GradeLevelType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class TeacherAssignmentSeeder extends Seeder
{
    private const ENROLLMENT_START = '2025-02-10';

    public function run(): void
    {
        $components = CurricularComponent::where('active', true)->get();
        $experienceFields = ExperienceField::where('active', true)->get();

        $schools = \App\Modules\SchoolStructure\Domain\Entities\School::orderBy('id')->get();

        foreach ($schools as $school) {
            $academicYear = AcademicYear::where('school_id', $school->id)
                ->where('year', 2025)
                ->first();

            if (! $academicYear) {
                continue;
            }

            $classGroups = ClassGroup::with('gradeLevel')
                ->where('academic_year_id', $academicYear->id)
                ->get();

            if ($classGroups->isEmpty()) {
                continue;
            }

            $teachers = Teacher::where('school_id', $school->id)
                ->where('active', true)
                ->get();

            if ($teachers->isEmpty()) {
                continue;
            }

            $this->assignSchool($classGroups, $teachers, $components, $experienceFields);
        }
    }

    private function assignSchool(
        Collection $classGroups,
        Collection $teachers,
        Collection $components,
        Collection $experienceFields,
    ): void {
        $polivalentIndex = 0;

        $specialistMap = [];
        foreach ($components as $i => $component) {
            $specialistMap[$component->id] = $teachers[$i % $teachers->count()];
        }

        foreach ($classGroups as $classGroup) {
            $type = $classGroup->gradeLevel->type;

            if ($type === GradeLevelType::EarlyChildhood) {
                $teacher = $teachers[$polivalentIndex % $teachers->count()];
                $polivalentIndex++;
                $this->assignPolivalentInfantil($classGroup, $teacher, $experienceFields);
                continue;
            }

            if ($type === GradeLevelType::ElementaryEarly) {
                $teacher = $teachers[$polivalentIndex % $teachers->count()];
                $polivalentIndex++;
                $this->assignPolivalentFundamental($classGroup, $teacher, $components);
                continue;
            }

            $this->assignSpecialist($classGroup, $components, $specialistMap);
        }
    }

    private function assignPolivalentInfantil(ClassGroup $classGroup, Teacher $teacher, Collection $experienceFields): void
    {
        foreach ($experienceFields as $field) {
            TeacherAssignment::updateOrCreate(
                [
                    'class_group_id' => $classGroup->id,
                    'experience_field_id' => $field->id,
                ],
                [
                    'teacher_id' => $teacher->id,
                    'curricular_component_id' => null,
                    'start_date' => self::ENROLLMENT_START,
                    'active' => true,
                ],
            );
        }
    }

    private function assignPolivalentFundamental(ClassGroup $classGroup, Teacher $teacher, Collection $components): void
    {
        foreach ($components as $component) {
            TeacherAssignment::updateOrCreate(
                [
                    'class_group_id' => $classGroup->id,
                    'curricular_component_id' => $component->id,
                ],
                [
                    'teacher_id' => $teacher->id,
                    'experience_field_id' => null,
                    'start_date' => self::ENROLLMENT_START,
                    'active' => true,
                ],
            );
        }
    }

    private function assignSpecialist(ClassGroup $classGroup, Collection $components, array $specialistMap): void
    {
        foreach ($components as $component) {
            $teacher = $specialistMap[$component->id];

            TeacherAssignment::updateOrCreate(
                [
                    'class_group_id' => $classGroup->id,
                    'curricular_component_id' => $component->id,
                ],
                [
                    'teacher_id' => $teacher->id,
                    'experience_field_id' => null,
                    'start_date' => self::ENROLLMENT_START,
                    'active' => true,
                ],
            );
        }
    }
}
