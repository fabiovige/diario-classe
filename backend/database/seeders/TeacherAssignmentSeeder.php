<?php

namespace Database\Seeders;

use App\Modules\Curriculum\Domain\Entities\CurricularComponent;
use App\Modules\Curriculum\Domain\Entities\ExperienceField;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\People\Domain\Entities\Teacher;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\SchoolStructure\Domain\Entities\GradeLevel;
use App\Modules\SchoolStructure\Domain\Entities\School;
use Illuminate\Database\Seeder;

class TeacherAssignmentSeeder extends Seeder
{
    private const ENROLLMENT_START = '2025-02-10';

    public function run(): void
    {
        $components = CurricularComponent::where('active', true)->get();
        $experienceFields = ExperienceField::where('active', true)->get();
        $infantilGradeLevelIds = GradeLevel::where('type', 'early_childhood')->pluck('id')->toArray();

        $schools = School::orderBy('id')->get();

        foreach ($schools as $school) {
            $academicYear = AcademicYear::where('school_id', $school->id)
                ->where('year', 2025)
                ->first();

            if (! $academicYear) {
                continue;
            }

            $classGroups = ClassGroup::where('academic_year_id', $academicYear->id)->get();

            if ($classGroups->isEmpty()) {
                continue;
            }

            $teachers = Teacher::where('school_id', $school->id)
                ->where('active', true)
                ->get();

            if ($teachers->isEmpty()) {
                continue;
            }

            $this->assignSchool($classGroups, $teachers, $components, $experienceFields, $infantilGradeLevelIds);
        }
    }

    private function assignSchool($classGroups, $teachers, $components, $experienceFields, array $infantilIds): void
    {
        $componentTeacherMap = [];
        foreach ($components as $i => $component) {
            $componentTeacherMap[$component->id] = $teachers[$i % $teachers->count()];
        }

        $fieldTeacherMap = [];
        foreach ($experienceFields as $i => $field) {
            $fieldTeacherMap[$field->id] = $teachers[$i % $teachers->count()];
        }

        foreach ($classGroups as $classGroup) {
            $isInfantil = in_array($classGroup->grade_level_id, $infantilIds, true);

            if ($isInfantil) {
                $this->assignInfantil($classGroup, $experienceFields, $fieldTeacherMap);
                continue;
            }

            $this->assignFundamental($classGroup, $components, $componentTeacherMap);
        }
    }

    private function assignInfantil($classGroup, $experienceFields, array $fieldTeacherMap): void
    {
        foreach ($experienceFields as $field) {
            $teacher = $fieldTeacherMap[$field->id];

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

    private function assignFundamental($classGroup, $components, array $componentTeacherMap): void
    {
        foreach ($components as $component) {
            $teacher = $componentTeacherMap[$component->id];

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
