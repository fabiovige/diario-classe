<?php

use App\Models\User;
use App\Modules\Attendance\Domain\Entities\AbsenceJustification;
use App\Modules\Attendance\Domain\Entities\AttendanceRecord;
use App\Modules\Attendance\Domain\Enums\AttendanceStatus;
use App\Modules\Curriculum\Domain\Entities\CurricularComponent;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\Identity\Domain\Entities\Role;
use App\Modules\Identity\Domain\Enums\RoleSlug;
use App\Modules\People\Domain\Entities\Student;
use App\Modules\People\Domain\Entities\Teacher;
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
    $this->students = Student::factory()->count(3)->create();
});

it('can record bulk attendance', function () {
    $records = $this->students->map(fn ($student) => [
        'student_id' => $student->id,
        'status' => 'present',
    ])->toArray();

    $response = $this->actingAs($this->admin)->postJson('/api/attendance/bulk', [
        'class_group_id' => $this->classGroup->id,
        'teacher_assignment_id' => $this->assignment->id,
        'date' => '2026-03-10',
        'records' => $records,
    ]);

    $response->assertCreated();
    expect(AttendanceRecord::count())->toBe(3);
});

it('can upsert bulk attendance', function () {
    $records = $this->students->map(fn ($student) => [
        'student_id' => $student->id,
        'status' => 'present',
    ])->toArray();

    $this->actingAs($this->admin)->postJson('/api/attendance/bulk', [
        'class_group_id' => $this->classGroup->id,
        'teacher_assignment_id' => $this->assignment->id,
        'date' => '2026-03-10',
        'records' => $records,
    ]);

    $records[0]['status'] = 'absent';
    $this->actingAs($this->admin)->postJson('/api/attendance/bulk', [
        'class_group_id' => $this->classGroup->id,
        'teacher_assignment_id' => $this->assignment->id,
        'date' => '2026-03-10',
        'records' => $records,
    ]);

    expect(AttendanceRecord::count())->toBe(3);
    expect(AttendanceRecord::where('student_id', $this->students[0]->id)->first()->status)
        ->toBe(AttendanceStatus::Absent);
});

it('can list attendance by class', function () {
    AttendanceRecord::factory()->create([
        'class_group_id' => $this->classGroup->id,
        'teacher_assignment_id' => $this->assignment->id,
        'student_id' => $this->students[0]->id,
        'date' => '2026-03-10',
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson("/api/attendance/class/{$this->classGroup->id}");

    $response->assertOk();
});

it('can get student frequency', function () {
    foreach (['2026-03-10', '2026-03-11', '2026-03-12'] as $date) {
        AttendanceRecord::factory()->create([
            'class_group_id' => $this->classGroup->id,
            'teacher_assignment_id' => $this->assignment->id,
            'student_id' => $this->students[0]->id,
            'date' => $date,
            'status' => AttendanceStatus::Present->value,
        ]);
    }

    $response = $this->actingAs($this->admin)
        ->getJson("/api/attendance/student/{$this->students[0]->id}/frequency?class_group_id={$this->classGroup->id}");

    $response->assertOk();
    expect((float) $response->json('data.frequency_percentage'))->toBe(100.0);
});

it('can create an absence justification', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/absence-justifications', [
        'student_id' => $this->students[0]->id,
        'start_date' => '2026-03-10',
        'end_date' => '2026-03-12',
        'reason' => 'Atestado médico',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.approved', false);
});

it('can approve an absence justification and update records', function () {
    AttendanceRecord::factory()->create([
        'class_group_id' => $this->classGroup->id,
        'teacher_assignment_id' => $this->assignment->id,
        'student_id' => $this->students[0]->id,
        'date' => '2026-03-10',
        'status' => AttendanceStatus::Absent->value,
    ]);

    $justification = AbsenceJustification::factory()->create([
        'student_id' => $this->students[0]->id,
        'start_date' => '2026-03-10',
        'end_date' => '2026-03-12',
    ]);

    $response = $this->actingAs($this->admin)
        ->postJson("/api/absence-justifications/{$justification->id}/approve");

    $response->assertOk()
        ->assertJsonPath('data.approved', true);

    $record = AttendanceRecord::where('student_id', $this->students[0]->id)->first();
    expect($record->status)->toBe(AttendanceStatus::JustifiedAbsence);
});

it('can create and list attendance configs', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/attendance-configs', [
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'consecutive_absences_alert' => 7,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.consecutive_absences_alert', 7);

    $listResponse = $this->actingAs($this->admin)->getJson('/api/attendance-configs');
    $listResponse->assertOk();
});
