<?php

use App\Models\User;
use App\Modules\Enrollment\Domain\Entities\Enrollment;
use App\Modules\Enrollment\Domain\Enums\EnrollmentStatus;
use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\Identity\Domain\Enums\RoleSlug;
use App\Modules\People\Domain\Entities\Student;
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
    $this->academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);
    $this->student = Student::factory()->create();
    $this->shift = Shift::factory()->create(['school_id' => $this->school->id]);
    $this->gradeLevel = GradeLevel::create(['name' => '1º Ano', 'type' => GradeLevelType::ElementaryEarly->value, 'order' => 7]);
    $this->classGroup = ClassGroup::factory()->create([
        'academic_year_id' => $this->academicYear->id,
        'grade_level_id' => $this->gradeLevel->id,
        'shift_id' => $this->shift->id,
    ]);
});

it('can create an enrollment', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/enrollments', [
        'student_id' => $this->student->id,
        'academic_year_id' => $this->academicYear->id,
        'school_id' => $this->school->id,
        'enrollment_date' => '2026-02-09',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'active');
});

it('can list enrollments', function () {
    Enrollment::factory()->count(2)->create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
    ]);

    $response = $this->actingAs($this->admin)->getJson('/api/enrollments');

    $response->assertOk();
});

it('can show an enrollment with movements', function () {
    $enrollment = Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'academic_year_id' => $this->academicYear->id,
        'school_id' => $this->school->id,
    ]);

    $response = $this->actingAs($this->admin)->getJson("/api/enrollments/{$enrollment->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $enrollment->id);
});

it('can assign a student to a class group', function () {
    $enrollment = Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'academic_year_id' => $this->academicYear->id,
        'school_id' => $this->school->id,
    ]);

    $response = $this->actingAs($this->admin)->postJson("/api/enrollments/{$enrollment->id}/assign-class", [
        'class_group_id' => $this->classGroup->id,
        'start_date' => '2026-02-09',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.class_group_id', $this->classGroup->id);
});

it('can transfer an enrollment', function () {
    $enrollment = Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'academic_year_id' => $this->academicYear->id,
        'school_id' => $this->school->id,
    ]);

    $response = $this->actingAs($this->admin)->postJson("/api/enrollments/{$enrollment->id}/transfer", [
        'type' => 'transferencia_externa',
        'movement_date' => '2026-06-15',
        'reason' => 'Mudança de cidade',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'transferred');
    expect($enrollment->refresh()->status)->toBe(EnrollmentStatus::Transferred);
});

it('cannot transfer an inactive enrollment', function () {
    $enrollment = Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'academic_year_id' => $this->academicYear->id,
        'school_id' => $this->school->id,
        'status' => EnrollmentStatus::Transferred->value,
    ]);

    $response = $this->actingAs($this->admin)->postJson("/api/enrollments/{$enrollment->id}/transfer", [
        'type' => 'transferencia_externa',
        'movement_date' => '2026-06-15',
    ]);

    $response->assertUnprocessable();
});

it('can view enrollment movements', function () {
    $enrollment = Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'academic_year_id' => $this->academicYear->id,
        'school_id' => $this->school->id,
    ]);

    $response = $this->actingAs($this->admin)->getJson("/api/enrollments/{$enrollment->id}/movements");

    $response->assertOk();
});
