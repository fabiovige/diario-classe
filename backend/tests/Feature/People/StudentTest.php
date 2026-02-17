<?php

use App\Models\User;
use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\Identity\Domain\Enums\RoleSlug;
use App\Modules\People\Domain\Entities\Guardian;
use App\Modules\People\Domain\Entities\Student;

beforeEach(function () {
    $role = Role::create(['name' => 'Admin', 'slug' => RoleSlug::Admin->value, 'permissions' => ['*']]);
    $this->admin = User::factory()->create(['role_id' => $role->id]);
});

it('can list students', function () {
    Student::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)->getJson('/api/students');

    $response->assertOk();
});

it('can create a student', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/students', [
        'name' => 'João Pedro Silva',
        'birth_date' => '2015-03-15',
        'gender' => 'male',
        'race_color' => 'parda',
        'birth_city' => 'Jandira',
        'birth_state' => 'SP',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'João Pedro Silva');
});

it('can show a student with guardians', function () {
    $student = Student::factory()->create();
    $guardian = Guardian::factory()->create();
    $student->guardians()->attach($guardian->id, [
        'relationship' => 'mae',
        'is_primary' => true,
        'can_pickup' => true,
    ]);

    $response = $this->actingAs($this->admin)->getJson("/api/students/{$student->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $student->id);
});

it('can update a student', function () {
    $student = Student::factory()->create();

    $response = $this->actingAs($this->admin)->putJson("/api/students/{$student->id}", [
        'name' => 'Nome Atualizado',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Nome Atualizado');
});

it('can attach a guardian to a student', function () {
    $student = Student::factory()->create();
    $guardian = Guardian::factory()->create();

    $response = $this->actingAs($this->admin)->postJson("/api/students/{$student->id}/guardians", [
        'guardian_id' => $guardian->id,
        'relationship' => 'mae',
        'is_primary' => true,
    ]);

    $response->assertOk();
    expect($student->guardians()->count())->toBe(1);
});
