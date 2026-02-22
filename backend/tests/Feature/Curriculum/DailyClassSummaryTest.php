<?php

use App\Models\User;
use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\Attendance\Domain\Entities\AttendanceRecord;
use App\Modules\ClassRecord\Domain\Entities\LessonRecord;
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
    $this->school = School::factory()->create();
    $this->academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);
    $this->shift = Shift::factory()->create(['school_id' => $this->school->id]);
    $this->gradeLevel = GradeLevel::create(['name' => '1º Ano', 'type' => GradeLevelType::ElementaryEarly->value, 'order' => 7]);
    $this->classGroup = ClassGroup::factory()->create([
        'academic_year_id' => $this->academicYear->id,
        'grade_level_id' => $this->gradeLevel->id,
        'shift_id' => $this->shift->id,
    ]);

    $teacherRole = Role::create(['name' => 'Professor', 'slug' => RoleSlug::Teacher->value, 'permissions' => ['*']]);
    $this->teacherUser = User::factory()->create(['role_id' => $teacherRole->id, 'school_id' => $this->school->id]);
    $this->teacher = Teacher::factory()->create([
        'user_id' => $this->teacherUser->id,
        'school_id' => $this->school->id,
    ]);

    $this->component = CurricularComponent::factory()->create();
    $this->assignment = TeacherAssignment::factory()->create([
        'teacher_id' => $this->teacher->id,
        'class_group_id' => $this->classGroup->id,
        'curricular_component_id' => $this->component->id,
        'active' => true,
    ]);

    $this->date = '2026-03-10';
});

it('returns daily summary with correct flags', function () {
    $student = Student::factory()->create();

    AttendanceRecord::factory()->create([
        'class_group_id' => $this->classGroup->id,
        'teacher_assignment_id' => $this->assignment->id,
        'student_id' => $student->id,
        'date' => $this->date,
    ]);

    LessonRecord::factory()->create([
        'class_group_id' => $this->classGroup->id,
        'teacher_assignment_id' => $this->assignment->id,
        'date' => $this->date,
    ]);

    AssessmentPeriod::factory()->create([
        'academic_year_id' => $this->academicYear->id,
        'start_date' => '2026-02-09',
        'end_date' => '2026-04-17',
        'status' => 'open',
    ]);

    $response = $this->actingAs($this->teacherUser)
        ->getJson("/api/teacher-assignments/daily-summary?date={$this->date}");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $this->assignment->id)
        ->assertJsonPath('data.0.has_attendance', true)
        ->assertJsonPath('data.0.has_lesson_record', true)
        ->assertJsonPath('data.0.has_open_period', true);
});

it('returns empty when teacher has no assignments', function () {
    $otherUser = User::factory()->create(['role_id' => $this->teacherUser->role_id, 'school_id' => $this->school->id]);
    $otherTeacher = Teacher::factory()->create([
        'user_id' => $otherUser->id,
        'school_id' => $this->school->id,
    ]);

    $response = $this->actingAs($otherUser)
        ->getJson("/api/teacher-assignments/daily-summary?date={$this->date}");

    $response->assertOk()
        ->assertJsonCount(0, 'data');
});

it('returns has_attendance false when no records for date', function () {
    $response = $this->actingAs($this->teacherUser)
        ->getJson("/api/teacher-assignments/daily-summary?date={$this->date}");

    $response->assertOk()
        ->assertJsonPath('data.0.has_attendance', false);
});

it('returns has_lesson_record false when no records for date', function () {
    $response = $this->actingAs($this->teacherUser)
        ->getJson("/api/teacher-assignments/daily-summary?date={$this->date}");

    $response->assertOk()
        ->assertJsonPath('data.0.has_lesson_record', false);
});

it('returns has_open_period true when open period contains date', function () {
    AssessmentPeriod::factory()->create([
        'academic_year_id' => $this->academicYear->id,
        'start_date' => '2026-02-09',
        'end_date' => '2026-04-17',
        'status' => 'open',
    ]);

    $response = $this->actingAs($this->teacherUser)
        ->getJson("/api/teacher-assignments/daily-summary?date={$this->date}");

    $response->assertOk()
        ->assertJsonPath('data.0.has_open_period', true);
});

it('returns has_open_period false when no open period contains date', function () {
    AssessmentPeriod::factory()->create([
        'academic_year_id' => $this->academicYear->id,
        'start_date' => '2026-02-09',
        'end_date' => '2026-04-17',
        'status' => 'closed',
    ]);

    $response = $this->actingAs($this->teacherUser)
        ->getJson("/api/teacher-assignments/daily-summary?date={$this->date}");

    $response->assertOk()
        ->assertJsonPath('data.0.has_open_period', false);
});

it('validates date is required', function () {
    $response = $this->actingAs($this->teacherUser)
        ->getJson('/api/teacher-assignments/daily-summary');

    $response->assertUnprocessable();
});
