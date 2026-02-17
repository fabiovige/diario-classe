<?php

use App\Models\User;
use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\Identity\Domain\Enums\RoleSlug;
use App\Modules\People\Domain\Entities\Teacher;
use App\Modules\SchoolStructure\Domain\Entities\School;

beforeEach(function () {
    $role = Role::create(['name' => 'Admin', 'slug' => RoleSlug::Admin->value, 'permissions' => ['*']]);
    $this->admin = User::factory()->create(['role_id' => $role->id]);
    $this->school = School::factory()->create();
});

it('can list teachers', function () {
    $teacherRole = Role::create(['name' => 'Professor', 'slug' => RoleSlug::Teacher->value, 'permissions' => []]);
    $teacherUser = User::factory()->create(['role_id' => $teacherRole->id, 'school_id' => $this->school->id]);
    Teacher::factory()->create(['user_id' => $teacherUser->id, 'school_id' => $this->school->id]);

    $response = $this->actingAs($this->admin)->getJson('/api/teachers');

    $response->assertOk();
});

it('can create a teacher', function () {
    $teacherRole = Role::create(['name' => 'Professor', 'slug' => RoleSlug::Teacher->value, 'permissions' => []]);
    $teacherUser = User::factory()->create(['role_id' => $teacherRole->id]);

    $response = $this->actingAs($this->admin)->postJson('/api/teachers', [
        'user_id' => $teacherUser->id,
        'school_id' => $this->school->id,
        'specialization' => 'Matemática',
        'hire_date' => '2020-02-01',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.specialization', 'Matemática');
});

it('can show a teacher', function () {
    $teacher = Teacher::factory()->create(['school_id' => $this->school->id]);

    $response = $this->actingAs($this->admin)->getJson("/api/teachers/{$teacher->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $teacher->id);
});
