<?php

use App\Models\User;
use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\Identity\Domain\Enums\RoleSlug;
use App\Modules\SchoolStructure\Domain\Entities\School;

beforeEach(function () {
    $role = Role::create(['name' => 'Admin', 'slug' => RoleSlug::Admin->value, 'permissions' => ['*']]);
    $this->admin = User::factory()->create(['role_id' => $role->id]);
});

it('can list schools', function () {
    School::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)->getJson('/api/schools');

    $response->assertOk();
});

it('can create a school', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/schools', [
        'name' => 'EMEB Professora Maria José',
        'inep_code' => '35123456',
        'type' => 'municipal',
        'address' => 'Rua das Flores, 100 - Jandira/SP',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'EMEB Professora Maria José');
});

it('can show a school', function () {
    $school = School::factory()->create();

    $response = $this->actingAs($this->admin)->getJson("/api/schools/{$school->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $school->id);
});

it('can update a school', function () {
    $school = School::factory()->create();

    $response = $this->actingAs($this->admin)->putJson("/api/schools/{$school->id}", [
        'name' => 'EMEB Atualizada',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'EMEB Atualizada');
});

it('can deactivate a school', function () {
    $school = School::factory()->create(['active' => true]);

    $response = $this->actingAs($this->admin)->deleteJson("/api/schools/{$school->id}");

    $response->assertNoContent();
    expect($school->refresh()->active)->toBeFalse();
});
