<?php

use App\Models\User;
use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\Identity\Domain\Enums\RoleSlug;

it('can login with valid credentials', function () {
    $role = Role::create(['name' => 'Admin', 'slug' => RoleSlug::Admin->value, 'permissions' => ['*']]);
    $user = User::factory()->create(['role_id' => $role->id, 'password' => 'secret123']);

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['data' => ['user', 'token']]);
});

it('rejects invalid credentials', function () {
    User::factory()->create(['password' => 'secret123']);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'wrong@email.com',
        'password' => 'wrong',
    ]);

    $response->assertUnprocessable();
});

it('rejects inactive user login', function () {
    $user = User::factory()->inactive()->create(['password' => 'secret123']);

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ]);

    $response->assertUnprocessable();
});

it('can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/auth/logout');

    $response->assertNoContent();
});

it('can get authenticated user profile', function () {
    $role = Role::create(['name' => 'Admin', 'slug' => RoleSlug::Admin->value, 'permissions' => ['*']]);
    $user = User::factory()->create(['role_id' => $role->id]);

    $response = $this->actingAs($user)->getJson('/api/auth/me');

    $response->assertOk()
        ->assertJsonPath('data.id', $user->id);
});
