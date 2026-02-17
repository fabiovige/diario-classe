<?php

use App\Models\User;
use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\Identity\Domain\Enums\RoleSlug;
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
    $this->shift = Shift::factory()->create(['school_id' => $this->school->id]);
    $this->academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);
    $this->gradeLevel = GradeLevel::create([
        'name' => '1º Ano',
        'type' => GradeLevelType::Elementary->value,
        'order' => 7,
    ]);
});

it('can create a class group', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/class-groups', [
        'academic_year_id' => $this->academicYear->id,
        'grade_level_id' => $this->gradeLevel->id,
        'shift_id' => $this->shift->id,
        'name' => 'A',
        'max_students' => 25,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'A');
});

it('can list class groups filtered by academic year', function () {
    ClassGroup::factory()->count(2)->create([
        'academic_year_id' => $this->academicYear->id,
        'grade_level_id' => $this->gradeLevel->id,
        'shift_id' => $this->shift->id,
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson("/api/class-groups?academic_year_id={$this->academicYear->id}");

    $response->assertOk();
});
