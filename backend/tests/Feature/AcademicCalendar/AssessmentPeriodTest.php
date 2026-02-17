<?php

use App\Models\User;
use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\AcademicCalendar\Domain\Enums\AssessmentPeriodStatus;
use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\Identity\Domain\Enums\RoleSlug;
use App\Modules\SchoolStructure\Domain\Entities\AcademicYear;
use App\Modules\SchoolStructure\Domain\Entities\School;

beforeEach(function () {
    $role = Role::create(['name' => 'Admin', 'slug' => RoleSlug::Admin->value, 'permissions' => ['*']]);
    $this->admin = User::factory()->create(['role_id' => $role->id]);
    $this->school = School::factory()->create();
    $this->academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);
});

it('can create an assessment period', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/assessment-periods', [
        'academic_year_id' => $this->academicYear->id,
        'type' => 'bimestral',
        'number' => 1,
        'name' => '1º Bimestre',
        'start_date' => '2026-02-09',
        'end_date' => '2026-04-17',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', '1º Bimestre')
        ->assertJsonPath('data.status', 'open')
        ->assertJsonPath('data.number', 1);
});

it('can list assessment periods', function () {
    AssessmentPeriod::factory()->count(4)->sequence(
        ['number' => 1, 'name' => '1º Bimestre'],
        ['number' => 2, 'name' => '2º Bimestre'],
        ['number' => 3, 'name' => '3º Bimestre'],
        ['number' => 4, 'name' => '4º Bimestre'],
    )->create(['academic_year_id' => $this->academicYear->id]);

    $response = $this->actingAs($this->admin)->getJson('/api/assessment-periods');

    $response->assertOk();
});

it('can filter assessment periods by academic year', function () {
    AssessmentPeriod::factory()->create(['academic_year_id' => $this->academicYear->id, 'number' => 1]);
    $otherYear = AcademicYear::factory()->create();
    AssessmentPeriod::factory()->create(['academic_year_id' => $otherYear->id, 'number' => 1]);

    $response = $this->actingAs($this->admin)
        ->getJson("/api/assessment-periods?academic_year_id={$this->academicYear->id}");

    $response->assertOk()
        ->assertJsonCount(1, 'data.data');
});

it('can show an assessment period', function () {
    $period = AssessmentPeriod::factory()->create(['academic_year_id' => $this->academicYear->id]);

    $response = $this->actingAs($this->admin)->getJson("/api/assessment-periods/{$period->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $period->id);
});

it('can update an assessment period', function () {
    $period = AssessmentPeriod::factory()->create(['academic_year_id' => $this->academicYear->id]);

    $response = $this->actingAs($this->admin)->putJson("/api/assessment-periods/{$period->id}", [
        'name' => '1º Bimestre Atualizado',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', '1º Bimestre Atualizado');
});

it('can transition status from open to closing', function () {
    $period = AssessmentPeriod::factory()->create([
        'academic_year_id' => $this->academicYear->id,
        'status' => AssessmentPeriodStatus::Open->value,
    ]);

    $response = $this->actingAs($this->admin)->putJson("/api/assessment-periods/{$period->id}", [
        'status' => 'closing',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'closing');
});

it('cannot transition status from open to closed directly', function () {
    $period = AssessmentPeriod::factory()->create([
        'academic_year_id' => $this->academicYear->id,
        'status' => AssessmentPeriodStatus::Open->value,
    ]);

    $response = $this->actingAs($this->admin)->putJson("/api/assessment-periods/{$period->id}", [
        'status' => 'closed',
    ]);

    $response->assertUnprocessable();
});

it('cannot update a closed period', function () {
    $period = AssessmentPeriod::factory()->create([
        'academic_year_id' => $this->academicYear->id,
        'status' => AssessmentPeriodStatus::Closed->value,
    ]);

    $response = $this->actingAs($this->admin)->putJson("/api/assessment-periods/{$period->id}", [
        'name' => 'Tentativa de alteração',
    ]);

    $response->assertUnprocessable();
});

it('validates period number against type max', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/assessment-periods', [
        'academic_year_id' => $this->academicYear->id,
        'type' => 'semestral',
        'number' => 3,
        'name' => '3º Semestre',
        'start_date' => '2026-02-09',
        'end_date' => '2026-04-17',
    ]);

    $response->assertUnprocessable();
});

it('validates required fields on creation', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/assessment-periods', []);

    $response->assertUnprocessable();
});

it('enforces unique constraint on academic_year + type + number', function () {
    AssessmentPeriod::factory()->create([
        'academic_year_id' => $this->academicYear->id,
        'type' => 'bimestral',
        'number' => 1,
    ]);

    $response = $this->actingAs($this->admin)->postJson('/api/assessment-periods', [
        'academic_year_id' => $this->academicYear->id,
        'type' => 'bimestral',
        'number' => 1,
        'name' => '1º Bimestre Duplicado',
        'start_date' => '2026-02-09',
        'end_date' => '2026-04-17',
    ]);

    $response->assertStatus(500);
});
