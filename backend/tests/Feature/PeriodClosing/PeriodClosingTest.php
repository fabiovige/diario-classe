<?php

use App\Models\User;
use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\Curriculum\Domain\Entities\CurricularComponent;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\Identity\Domain\Enums\RoleSlug;
use App\Modules\People\Domain\Entities\Teacher;
use App\Modules\PeriodClosing\Domain\Entities\PeriodClosing;
use App\Modules\PeriodClosing\Domain\Enums\ClosingStatus;
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
    $this->shift = Shift::factory()->create(['school_id' => $this->school->id]);
    $this->gradeLevel = GradeLevel::create(['name' => '1º Ano', 'type' => GradeLevelType::Elementary->value, 'order' => 7]);
    $this->classGroup = ClassGroup::factory()->create([
        'academic_year_id' => $this->academicYear->id,
        'grade_level_id' => $this->gradeLevel->id,
        'shift_id' => $this->shift->id,
    ]);
    $this->teacher = Teacher::factory()->create(['school_id' => $this->school->id]);
    $this->component = CurricularComponent::factory()->create();
    $this->assignment = TeacherAssignment::factory()->create([
        'teacher_id' => $this->teacher->id,
        'class_group_id' => $this->classGroup->id,
        'curricular_component_id' => $this->component->id,
    ]);
    $this->period = AssessmentPeriod::factory()->create([
        'academic_year_id' => $this->academicYear->id,
    ]);
    $this->closing = PeriodClosing::create([
        'class_group_id' => $this->classGroup->id,
        'teacher_assignment_id' => $this->assignment->id,
        'assessment_period_id' => $this->period->id,
        'status' => ClosingStatus::Pending->value,
    ]);
});

it('can list period closings', function () {
    $response = $this->actingAs($this->admin)->getJson('/api/period-closings');

    $response->assertOk();
});

it('can run completeness check', function () {
    $response = $this->actingAs($this->admin)
        ->postJson("/api/period-closings/{$this->closing->id}/check");

    $response->assertOk()
        ->assertJsonPath('data.id', $this->closing->id);
});

it('cannot submit without completeness checks passing', function () {
    $response = $this->actingAs($this->admin)
        ->postJson("/api/period-closings/{$this->closing->id}/submit");

    $response->assertUnprocessable();
});

it('can submit after completeness checks pass', function () {
    $this->closing->update([
        'all_grades_complete' => true,
        'all_attendance_complete' => true,
        'all_lesson_records_complete' => true,
    ]);

    $response = $this->actingAs($this->admin)
        ->postJson("/api/period-closings/{$this->closing->id}/submit");

    $response->assertOk()
        ->assertJsonPath('data.status', 'in_validation');
});

it('can validate (approve) a submitted closing', function () {
    $this->closing->update([
        'status' => ClosingStatus::InValidation->value,
        'all_grades_complete' => true,
        'all_attendance_complete' => true,
        'all_lesson_records_complete' => true,
    ]);

    $response = $this->actingAs($this->admin)
        ->postJson("/api/period-closings/{$this->closing->id}/validate", [
            'approve' => true,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'approved');
});

it('can reject a submitted closing', function () {
    $this->closing->update([
        'status' => ClosingStatus::InValidation->value,
        'all_grades_complete' => true,
        'all_attendance_complete' => true,
        'all_lesson_records_complete' => true,
    ]);

    $response = $this->actingAs($this->admin)
        ->postJson("/api/period-closings/{$this->closing->id}/validate", [
            'approve' => false,
            'rejection_reason' => 'Faltam registros de aula',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.rejection_reason', 'Faltam registros de aula');
});

it('can close an approved period', function () {
    $this->closing->update(['status' => ClosingStatus::Approved->value]);

    $response = $this->actingAs($this->admin)
        ->postJson("/api/period-closings/{$this->closing->id}/close");

    $response->assertOk()
        ->assertJsonPath('data.status', 'closed');
});

it('cannot close a pending period', function () {
    $response = $this->actingAs($this->admin)
        ->postJson("/api/period-closings/{$this->closing->id}/close");

    $response->assertUnprocessable();
});

it('can request a rectification on closed period', function () {
    $this->closing->update(['status' => ClosingStatus::Closed->value]);

    $response = $this->actingAs($this->admin)->postJson('/api/rectifications', [
        'period_closing_id' => $this->closing->id,
        'entity_type' => 'grade',
        'entity_id' => 1,
        'field_changed' => 'numeric_value',
        'old_value' => '5.0',
        'new_value' => '7.0',
        'justification' => 'Erro de digitação',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'requested');
});

it('cannot request rectification on non-closed period', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/rectifications', [
        'period_closing_id' => $this->closing->id,
        'entity_type' => 'grade',
        'entity_id' => 1,
        'field_changed' => 'numeric_value',
        'old_value' => '5.0',
        'new_value' => '7.0',
        'justification' => 'Erro',
    ]);

    $response->assertUnprocessable();
});
