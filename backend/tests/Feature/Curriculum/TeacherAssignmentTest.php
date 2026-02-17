<?php

use App\Models\User;
use App\Modules\Curriculum\Domain\Entities\CurricularComponent;
use App\Modules\Curriculum\Domain\Entities\ExperienceField;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\Identity\Domain\Enums\RoleSlug;
use App\Modules\People\Domain\Entities\Teacher;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\SchoolStructure\Domain\Entities\GradeLevel;
use App\Modules\SchoolStructure\Domain\Entities\School;
use App\Modules\SchoolStructure\Domain\Entities\Shift;
use App\Modules\SchoolStructure\Domain\Enums\GradeLevelType;

beforeEach(function () {
    $role = Role::create(['name' => 'Admin', 'slug' => RoleSlug::Admin->value, 'permissions' => ['*']]);
    $this->admin = User::factory()->create(['role_id' => $role->id]);
    $this->school = School::factory()->create();
    $this->academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);
    $this->shift = Shift::factory()->create(['school_id' => $this->school->id]);
    $this->gradeLevel = GradeLevel::create(['name' => '1º Ano', 'type' => GradeLevelType::Elementary->value, 'order' => 7]);
    $this->classGroup = ClassGroup::factory()->create([
        'academic_year_id' => $this->academicYear->id,
        'grade_level_id' => $this->gradeLevel->id,
        'shift_id' => $this->shift->id,
    ]);
    $this->teacher = Teacher::factory()->create(['school_id' => $this->school->id]);
    $this->component = CurricularComponent::factory()->create();
});

it('can create a teacher assignment with curricular component', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/teacher-assignments', [
        'teacher_id' => $this->teacher->id,
        'class_group_id' => $this->classGroup->id,
        'curricular_component_id' => $this->component->id,
        'start_date' => '2026-02-09',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.teacher_id', $this->teacher->id)
        ->assertJsonPath('data.curricular_component_id', $this->component->id);
});

it('can create a teacher assignment with experience field', function () {
    $field = ExperienceField::factory()->create();

    $response = $this->actingAs($this->admin)->postJson('/api/teacher-assignments', [
        'teacher_id' => $this->teacher->id,
        'class_group_id' => $this->classGroup->id,
        'experience_field_id' => $field->id,
        'start_date' => '2026-02-09',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.experience_field_id', $field->id);
});

it('rejects assignment with both component and field', function () {
    $field = ExperienceField::factory()->create();

    $response = $this->actingAs($this->admin)->postJson('/api/teacher-assignments', [
        'teacher_id' => $this->teacher->id,
        'class_group_id' => $this->classGroup->id,
        'curricular_component_id' => $this->component->id,
        'experience_field_id' => $field->id,
        'start_date' => '2026-02-09',
    ]);

    $response->assertUnprocessable();
});

it('rejects assignment with neither component nor field', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/teacher-assignments', [
        'teacher_id' => $this->teacher->id,
        'class_group_id' => $this->classGroup->id,
        'start_date' => '2026-02-09',
    ]);

    $response->assertUnprocessable();
});

it('rejects assignment when teacher is from different school', function () {
    $otherSchool = School::factory()->create();
    $otherTeacher = Teacher::factory()->create(['school_id' => $otherSchool->id]);

    $response = $this->actingAs($this->admin)->postJson('/api/teacher-assignments', [
        'teacher_id' => $otherTeacher->id,
        'class_group_id' => $this->classGroup->id,
        'curricular_component_id' => $this->component->id,
        'start_date' => '2026-02-09',
    ]);

    $response->assertUnprocessable();
});

it('can list teacher assignments', function () {
    TeacherAssignment::factory()->count(2)->create([
        'teacher_id' => $this->teacher->id,
        'class_group_id' => $this->classGroup->id,
    ]);

    $response = $this->actingAs($this->admin)->getJson('/api/teacher-assignments');

    $response->assertOk();
});

it('can update a teacher assignment', function () {
    $assignment = TeacherAssignment::factory()->create([
        'teacher_id' => $this->teacher->id,
        'class_group_id' => $this->classGroup->id,
    ]);

    $response = $this->actingAs($this->admin)->putJson("/api/teacher-assignments/{$assignment->id}", [
        'active' => false,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.active', false);
});
