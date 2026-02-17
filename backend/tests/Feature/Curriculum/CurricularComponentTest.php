<?php

use App\Models\User;
use App\Modules\Curriculum\Domain\Entities\CurricularComponent;
use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\Identity\Domain\Enums\RoleSlug;

beforeEach(function () {
    $role = Role::create(['name' => 'Admin', 'slug' => RoleSlug::Admin->value, 'permissions' => ['*']]);
    $this->admin = User::factory()->create(['role_id' => $role->id]);
});

it('can create a curricular component', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/curricular-components', [
        'name' => 'Língua Portuguesa',
        'knowledge_area' => 'linguagens',
        'code' => 'LP',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Língua Portuguesa')
        ->assertJsonPath('data.knowledge_area', 'linguagens');
});

it('can list curricular components', function () {
    CurricularComponent::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)->getJson('/api/curricular-components');

    $response->assertOk();
});

it('can show a curricular component', function () {
    $component = CurricularComponent::factory()->create();

    $response = $this->actingAs($this->admin)->getJson("/api/curricular-components/{$component->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $component->id);
});

it('validates unique name on creation', function () {
    CurricularComponent::factory()->create(['name' => 'Matemática']);

    $response = $this->actingAs($this->admin)->postJson('/api/curricular-components', [
        'name' => 'Matemática',
        'knowledge_area' => 'matematica',
    ]);

    $response->assertUnprocessable();
});

it('validates required fields on creation', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/curricular-components', []);

    $response->assertUnprocessable();
});
