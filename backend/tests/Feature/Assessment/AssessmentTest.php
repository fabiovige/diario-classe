<?php

use App\Models\User;
use App\Modules\AcademicCalendar\Domain\Entities\AssessmentPeriod;
use App\Modules\Assessment\Domain\Entities\AssessmentConfig;
use App\Modules\Assessment\Domain\Entities\AssessmentInstrument;
use App\Modules\Assessment\Domain\Entities\Grade;
use App\Modules\Curriculum\Domain\Entities\CurricularComponent;
use App\Modules\Curriculum\Domain\Entities\TeacherAssignment;
use App\Modules\Enrollment\Domain\Entities\ClassAssignment;
use App\Modules\Enrollment\Domain\Entities\Enrollment;
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
    $this->gradeLevel = GradeLevel::create(['name' => '1º Ano', 'type' => GradeLevelType::ElementaryEarly->value, 'order' => 7]);
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
    $this->config = AssessmentConfig::create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'grade_level_id' => $this->gradeLevel->id,
        'grade_type' => 'numeric',
        'scale_min' => 0,
        'scale_max' => 10,
        'passing_grade' => 6,
        'average_formula' => 'arithmetic',
        'rounding_precision' => 1,
        'recovery_enabled' => true,
        'recovery_replaces' => 'higher',
    ]);
    $this->instrument = AssessmentInstrument::create([
        'assessment_config_id' => $this->config->id,
        'name' => 'Prova',
        'weight' => 1,
        'max_value' => 10,
        'order' => 1,
    ]);
    $this->students = Student::factory()->count(3)->create();
});

it('can record bulk grades', function () {
    $grades = $this->students->map(fn ($student) => [
        'student_id' => $student->id,
        'numeric_value' => fake()->randomFloat(1, 0, 10),
    ])->toArray();

    $response = $this->actingAs($this->admin)->postJson('/api/grades/bulk', [
        'class_group_id' => $this->classGroup->id,
        'teacher_assignment_id' => $this->assignment->id,
        'assessment_period_id' => $this->period->id,
        'assessment_instrument_id' => $this->instrument->id,
        'grades' => $grades,
    ]);

    $response->assertCreated();
    expect(Grade::count())->toBe(3);
});

it('can list grades', function () {
    Grade::factory()->count(2)->create([
        'class_group_id' => $this->classGroup->id,
        'teacher_assignment_id' => $this->assignment->id,
        'assessment_period_id' => $this->period->id,
        'assessment_instrument_id' => $this->instrument->id,
    ]);

    $response = $this->actingAs($this->admin)->getJson('/api/grades');

    $response->assertOk();
});

it('can record a recovery grade', function () {
    $student = $this->students[0];

    Grade::factory()->create([
        'student_id' => $student->id,
        'class_group_id' => $this->classGroup->id,
        'teacher_assignment_id' => $this->assignment->id,
        'assessment_period_id' => $this->period->id,
        'assessment_instrument_id' => $this->instrument->id,
        'numeric_value' => 4.0,
    ]);

    $response = $this->actingAs($this->admin)->postJson('/api/grades/recovery', [
        'student_id' => $student->id,
        'class_group_id' => $this->classGroup->id,
        'teacher_assignment_id' => $this->assignment->id,
        'assessment_period_id' => $this->period->id,
        'assessment_instrument_id' => $this->instrument->id,
        'numeric_value' => 7.0,
        'recovery_type' => 'parallel',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.is_recovery', true)
        ->assertJsonPath('data.recovery_type', 'parallel');
});

it('can calculate period average', function () {
    $student = $this->students[0];

    Grade::factory()->create([
        'student_id' => $student->id,
        'class_group_id' => $this->classGroup->id,
        'teacher_assignment_id' => $this->assignment->id,
        'assessment_period_id' => $this->period->id,
        'assessment_instrument_id' => $this->instrument->id,
        'numeric_value' => 8.0,
        'is_recovery' => false,
    ]);

    $response = $this->actingAs($this->admin)->postJson('/api/period-averages/calculate', [
        'student_id' => $student->id,
        'class_group_id' => $this->classGroup->id,
        'teacher_assignment_id' => $this->assignment->id,
        'assessment_period_id' => $this->period->id,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.numeric_average', '8.00');
});

it('can create and list assessment configs', function () {
    $newGradeLevel = GradeLevel::create(['name' => '2º Ano', 'type' => GradeLevelType::ElementaryEarly->value, 'order' => 8]);

    $response = $this->actingAs($this->admin)->postJson('/api/assessment-configs', [
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'grade_level_id' => $newGradeLevel->id,
        'grade_type' => 'numeric',
        'scale_min' => 0,
        'scale_max' => 10,
        'passing_grade' => 6,
    ]);

    $response->assertCreated();

    $listResponse = $this->actingAs($this->admin)->getJson('/api/assessment-configs');
    $listResponse->assertOk();
});

it('can get student report card', function () {
    $student = $this->students[0];

    $enrollment = Enrollment::create([
        'student_id' => $student->id,
        'academic_year_id' => $this->academicYear->id,
        'school_id' => $this->school->id,
        'enrollment_number' => 'RA-0001',
        'enrollment_type' => 'new_enrollment',
        'status' => 'active',
        'enrollment_date' => now(),
    ]);

    ClassAssignment::create([
        'enrollment_id' => $enrollment->id,
        'class_group_id' => $this->classGroup->id,
        'status' => 'active',
        'start_date' => now(),
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson("/api/report-cards/student/{$student->id}");

    $response->assertOk()
        ->assertJsonStructure(['data' => [
            'student' => ['id', 'name', 'display_name', 'birth_date', 'class_group', 'enrollment_number', 'school_name', 'academic_year'],
            'assessment_periods',
            'subjects',
            'descriptive_reports',
        ]]);
});

it('validates required fields for bulk grades', function () {
    $response = $this->actingAs($this->admin)->postJson('/api/grades/bulk', []);

    $response->assertUnprocessable();
});
