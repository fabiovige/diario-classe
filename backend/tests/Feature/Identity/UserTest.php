<?php

use App\Models\User;
use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\Identity\Domain\Enums\RoleSlug;
use App\Modules\Identity\Domain\Enums\UserStatus;

beforeEach(function () {
    $this->adminRole = Role::create(['name' => 'Admin', 'slug' => RoleSlug::Admin->value, 'permissions' => ['*']]);
    $this->admin = User::factory()->create(['role_id' => $this->adminRole->id]);
});

it('can list users', function () {
    User::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)->getJson('/api/users');

    $response->assertOk()
        ->assertJsonStructure(['data' => ['data']]);
});

it('can create a user', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/users', [
        'name' => 'Maria Silva',
        'email' => 'maria@escola.sp.gov.br',
        'password' => 'senha1234',
        'role_id' => $this->adminRole->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Maria Silva');
});

it('can show a user', function () {
    $user = User::factory()->create(['role_id' => $this->adminRole->id]);

    $response = $this->actingAs($this->admin)->getJson("/api/users/{$user->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $user->id);
});

it('can update a user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($this->admin)->putJson("/api/users/{$user->id}", [
        'name' => 'Nome Atualizado',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Nome Atualizado');
});

it('can soft-delete a user by setting inactive', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($this->admin)->deleteJson("/api/users/{$user->id}");

    $response->assertNoContent();
    expect($user->refresh()->status)->toBe(UserStatus::Inactive);
});
