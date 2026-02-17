<?php

use App\Models\User;
use App\Modules\ClassRecord\Domain\Entities\LessonRecord;
use App\Modules\Curriculum\Domain\Entities\CurricularComponent;
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
    $this->assignment = TeacherAssignment::factory()->create([
        'teacher_id' => $this->teacher->id,
        'class_group_id' => $this->classGroup->id,
        'curricular_component_id' => $this->component->id,
    ]);
});

it('can create a lesson record', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/lesson-records', [
        'class_group_id' => $this->classGroup->id,
        'teacher_assignment_id' => $this->assignment->id,
        'date' => '2026-03-10',
        'content' => 'Introdução às frações',
        'methodology' => 'Aula expositiva com material concreto',
        'class_count' => 2,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.content', 'Introdução às frações')
        ->assertJsonPath('data.class_count', 2);
});

it('can list lesson records', function () {
    LessonRecord::factory()->count(3)->create([
        'class_group_id' => $this->classGroup->id,
        'teacher_assignment_id' => $this->assignment->id,
    ]);

    $response = $this->actingAs($this->admin)->getJson('/api/lesson-records');

    $response->assertOk();
});

it('can show a lesson record', function () {
    $record = LessonRecord::factory()->create([
        'class_group_id' => $this->classGroup->id,
        'teacher_assignment_id' => $this->assignment->id,
    ]);

    $response = $this->actingAs($this->admin)->getJson("/api/lesson-records/{$record->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $record->id);
});

it('can update a lesson record', function () {
    $record = LessonRecord::factory()->create([
        'class_group_id' => $this->classGroup->id,
        'teacher_assignment_id' => $this->assignment->id,
    ]);

    $response = $this->actingAs($this->admin)->putJson("/api/lesson-records/{$record->id}", [
        'content' => 'Conteúdo atualizado',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.content', 'Conteúdo atualizado');
});

it('validates required fields on creation', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/lesson-records', []);

    $response->assertUnprocessable();
});

it('can filter lesson records by class group', function () {
    LessonRecord::factory()->create([
        'class_group_id' => $this->classGroup->id,
        'teacher_assignment_id' => $this->assignment->id,
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson("/api/lesson-records?class_group_id={$this->classGroup->id}");

    $response->assertOk()
        ->assertJsonCount(1, 'data.data');
});
