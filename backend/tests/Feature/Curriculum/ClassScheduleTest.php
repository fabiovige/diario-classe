<?php

use App\Models\User;
use App\Modules\Curriculum\Domain\Entities\ClassSchedule;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\Curriculum\Domain\Entities\TimeSlot;
use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\Identity\Domain\Enums\RoleSlug;
use App\Modules\SchoolStructure\Domain\Entities\ClassGroup;
use App\Modules\SchoolStructure\Domain\Entities\Shift;

beforeEach(function () {
    $role = Role::create(['name' => 'Admin', 'slug' => RoleSlug::Admin->value, 'permissions' => ['*']]);
    $this->admin = User::factory()->create(['role_id' => $role->id]);

    $this->shift = Shift::factory()->create();
    $this->classGroup = ClassGroup::factory()->create(['shift_id' => $this->shift->id]);
    $this->assignment = TeacherAssignment::factory()->create([
        'class_group_id' => $this->classGroup->id,
    ]);

    $this->slots = collect();
    for ($i = 1; $i <= 5; $i++) {
        $this->slots->push(TimeSlot::factory()->create([
            'shift_id' => $this->shift->id,
            'number' => $i,
            'start_time' => sprintf('%02d:00', 6 + $i),
            'end_time' => sprintf('%02d:50', 6 + $i),
        ]));
    }
});

it('can save a schedule for an assignment', function () {
    $response = $this->actingAs($this->admin)->putJson(
        "/api/class-schedules/assignment/{$this->assignment->id}",
        [
            'slots' => [
                ['time_slot_id' => $this->slots[0]->id, 'day_of_week' => 1],
                ['time_slot_id' => $this->slots[1]->id, 'day_of_week' => 2],
            ],
        ]
    );

    $response->assertOk()
        ->assertJsonCount(2, 'data');

    expect(ClassSchedule::where('teacher_assignment_id', $this->assignment->id)->count())->toBe(2);
});

it('can list schedules by class group', function () {
    ClassSchedule::factory()->create([
        'teacher_assignment_id' => $this->assignment->id,
        'time_slot_id' => $this->slots[0]->id,
        'day_of_week' => 1,
    ]);

    $response = $this->actingAs($this->admin)->getJson(
        "/api/class-schedules?class_group_id={$this->classGroup->id}"
    );

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

it('can list schedules by teacher', function () {
    ClassSchedule::factory()->create([
        'teacher_assignment_id' => $this->assignment->id,
        'time_slot_id' => $this->slots[0]->id,
        'day_of_week' => 1,
    ]);

    $response = $this->actingAs($this->admin)->getJson(
        "/api/class-schedules?teacher_id={$this->assignment->teacher_id}"
    );

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

it('prevents class group conflict on same slot and day', function () {
    $otherAssignment = TeacherAssignment::factory()->create([
        'class_group_id' => $this->classGroup->id,
    ]);

    ClassSchedule::factory()->create([
        'teacher_assignment_id' => $otherAssignment->id,
        'time_slot_id' => $this->slots[0]->id,
        'day_of_week' => 1,
    ]);

    $response = $this->actingAs($this->admin)->putJson(
        "/api/class-schedules/assignment/{$this->assignment->id}",
        [
            'slots' => [
                ['time_slot_id' => $this->slots[0]->id, 'day_of_week' => 1],
            ],
        ]
    );

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('slots');
});

it('prevents teacher conflict on same slot and day across classes', function () {
    $otherClassGroup = ClassGroup::factory()->create(['shift_id' => $this->shift->id]);
    $otherAssignment = TeacherAssignment::factory()->create([
        'teacher_id' => $this->assignment->teacher_id,
        'class_group_id' => $otherClassGroup->id,
    ]);

    ClassSchedule::factory()->create([
        'teacher_assignment_id' => $otherAssignment->id,
        'time_slot_id' => $this->slots[0]->id,
        'day_of_week' => 1,
    ]);

    $response = $this->actingAs($this->admin)->putJson(
        "/api/class-schedules/assignment/{$this->assignment->id}",
        [
            'slots' => [
                ['time_slot_id' => $this->slots[0]->id, 'day_of_week' => 1],
            ],
        ]
    );

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('slots');
});

it('prevents slot from wrong shift', function () {
    $otherShift = Shift::factory()->create();
    $wrongSlot = TimeSlot::factory()->create([
        'shift_id' => $otherShift->id,
        'number' => 1,
    ]);

    $response = $this->actingAs($this->admin)->putJson(
        "/api/class-schedules/assignment/{$this->assignment->id}",
        [
            'slots' => [
                ['time_slot_id' => $wrongSlot->id, 'day_of_week' => 1],
            ],
        ]
    );

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('slots');
});

it('syncs schedule replacing old entries', function () {
    ClassSchedule::factory()->create([
        'teacher_assignment_id' => $this->assignment->id,
        'time_slot_id' => $this->slots[0]->id,
        'day_of_week' => 1,
    ]);

    $response = $this->actingAs($this->admin)->putJson(
        "/api/class-schedules/assignment/{$this->assignment->id}",
        [
            'slots' => [
                ['time_slot_id' => $this->slots[1]->id, 'day_of_week' => 3],
                ['time_slot_id' => $this->slots[2]->id, 'day_of_week' => 4],
            ],
        ]
    );

    $response->assertOk()
        ->assertJsonCount(2, 'data');

    expect(ClassSchedule::where('teacher_assignment_id', $this->assignment->id)->count())->toBe(2);
});
