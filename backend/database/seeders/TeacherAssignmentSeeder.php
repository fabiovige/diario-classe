<?php

namespace Database\Seeders;

use App\Modules\Curriculum\Domain\Entities\CurricularComponent;
use App\Modules\Curriculum\Domain\Entities\ExperienceField;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\People\Domain\Entities\Teacher;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\SchoolStructure\Domain\Entities\GradeLevel;
use Illuminate\Database\Seeder;

class TeacherAssignmentSeeder extends Seeder
{
    private const ENROLLMENT_START = '2026-02-09';

    public function run(): void
    {
        $teachers = Teacher::where('active', true)->with('school')->get();
        $components = CurricularComponent::where('active', true)->get();
        $experienceFields = ExperienceField::where('active', true)->get();

        $infantilGradeLevelIds = GradeLevel::where('type', 'early_childhood')->pluck('id');

        foreach ($teachers as $teacher) {
            $classGroups = ClassGroup::whereHas('academicYear', function ($q) use ($teacher) {
                $q->where('school_id', $teacher->school_id);
            })->with('gradeLevel')->inRandomOrder()->limit(3)->get();

            foreach ($classGroups as $classGroup) {
                $isInfantil = $infantilGradeLevelIds->contains($classGroup->grade_level_id);

                if ($isInfantil) {
                    $field = $experienceFields->random();
                    TeacherAssignment::updateOrCreate(
                        [
                            'teacher_id' => $teacher->id,
                            'class_group_id' => $classGroup->id,
                            'experience_field_id' => $field->id,
                        ],
                        [
                            'curricular_component_id' => null,
                            'start_date' => self::ENROLLMENT_START,
                            'active' => true,
                        ],
                    );

                    continue;
                }

                $component = $components->random();
                TeacherAssignment::updateOrCreate(
                    [
                        'teacher_id' => $teacher->id,
                        'class_group_id' => $classGroup->id,
                        'curricular_component_id' => $component->id,
                    ],
                    [
                        'experience_field_id' => null,
                        'start_date' => self::ENROLLMENT_START,
                        'active' => true,
                    ],
                );
            }
        }
    }
}
