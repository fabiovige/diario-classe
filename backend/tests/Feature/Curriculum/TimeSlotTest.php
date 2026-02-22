<?php

use App\Models\User;
use App\Modules\Curriculum\Domain\Entities\TimeSlot;
use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\Identity\Domain\Enums\RoleSlug;
use App\Modules\SchoolStructure\Domain\Entities\Shift;

beforeEach(function () {
    $role = Role::create(['name' => 'Admin', 'slug' => RoleSlug::Admin->value, 'permissions' => ['*']]);
    $this->admin = User::factory()->create(['role_id' => $role->id]);
    $this->shift = Shift::factory()->create();
});

it('can list time slots by shift', function () {
    for ($i = 1; $i <= 5; $i++) {
        TimeSlot::factory()->create(['shift_id' => $this->shift->id, 'number' => $i]);
    }

    $response = $this->actingAs($this->admin)->getJson("/api/time-slots?shift_id={$this->shift->id}");

    $response->assertOk()
        ->assertJsonCount(5, 'data');
});

it('can create a time slot', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/time-slots', [
        'shift_id' => $this->shift->id,
        'number' => 1,
        'start_time' => '07:00',
        'end_time' => '07:50',
        'type' => 'class',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.number', 1)
        ->assertJsonPath('data.type', 'class')
        ->assertJsonPath('data.type_label', 'Aula');
});

it('can show a time slot', function () {
    $slot = TimeSlot::factory()->create(['shift_id' => $this->shift->id, 'number' => 1]);

    $response = $this->actingAs($this->admin)->getJson("/api/time-slots/{$slot->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $slot->id);
});

it('can update a time slot', function () {
    $slot = TimeSlot::factory()->create(['shift_id' => $this->shift->id, 'number' => 2]);

    $response = $this->actingAs($this->admin)->putJson("/api/time-slots/{$slot->id}", [
        'start_time' => '08:00',
        'end_time' => '08:50',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.start_time', '08:00');
});

it('can delete a time slot', function () {
    $slot = TimeSlot::factory()->create(['shift_id' => $this->shift->id, 'number' => 3]);

    $response = $this->actingAs($this->admin)->deleteJson("/api/time-slots/{$slot->id}");

    $response->assertNoContent();
    expect(TimeSlot::find($slot->id))->toBeNull();
});

it('validates required fields on creation', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/time-slots', []);

    $response->assertUnprocessable();
});

it('validates end_time must be after start_time', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/time-slots', [
        'shift_id' => $this->shift->id,
        'number' => 1,
        'start_time' => '08:00',
        'end_time' => '07:00',
        'type' => 'class',
    ]);

    $response->assertUnprocessable();
});
