<?php

use App\Modules\AcademicCalendar\Presentation\Controllers\AssessmentPeriodController;
use App\Modules\Assessment\Presentation\Controllers\AssessmentController;
use App\Modules\Attendance\Presentation\Controllers\AttendanceController;
use App\Modules\PeriodClosing\Presentation\Controllers\PeriodClosingController;
use App\Modules\ClassRecord\Presentation\Controllers\LessonRecordController;
use App\Modules\Curriculum\Presentation\Controllers\CurricularComponentController;
use App\Modules\Curriculum\Presentation\Controllers\ExperienceFieldController;
use App\Modules\Curriculum\Presentation\Controllers\TeacherAssignmentController;
use App\Modules\Enrollment\Presentation\Controllers\EnrollmentController;
use App\Modules\Identity\Presentation\Controllers\AuthController;
use App\Modules\Identity\Presentation\Controllers\RoleController;
use App\Modules\Identity\Presentation\Controllers\UserController;
use App\Modules\People\Presentation\Controllers\GuardianController;
use App\Modules\People\Presentation\Controllers\StudentController;
use App\Modules\People\Presentation\Controllers\TeacherController;
use App\Modules\SchoolStructure\Presentation\Controllers\AcademicYearController;
use App\Modules\SchoolStructure\Presentation\Controllers\ClassGroupController;
use App\Modules\SchoolStructure\Presentation\Controllers\GradeLevelController;
use App\Modules\SchoolStructure\Presentation\Controllers\SchoolController;
use App\Modules\SchoolStructure\Presentation\Controllers\ShiftController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);

    Route::apiResource('schools', SchoolController::class);
    Route::apiResource('academic-years', AcademicYearController::class)->except(['destroy']);
    Route::apiResource('shifts', ShiftController::class);
    Route::get('grade-levels', [GradeLevelController::class, 'index']);
    Route::get('grade-levels/{gradeLevel}', [GradeLevelController::class, 'show']);
    Route::apiResource('class-groups', ClassGroupController::class);

    Route::apiResource('students', StudentController::class)->except(['destroy']);
    Route::post('students/{student}/guardians', [StudentController::class, 'attachGuardian']);
    Route::apiResource('guardians', GuardianController::class)->except(['destroy']);
    Route::apiResource('teachers', TeacherController::class)->except(['destroy']);

    Route::apiResource('enrollments', EnrollmentController::class)->only(['index', 'store', 'show']);
    Route::post('enrollments/{enrollment}/assign-class', [EnrollmentController::class, 'assignToClass']);
    Route::post('enrollments/{enrollment}/transfer', [EnrollmentController::class, 'transfer']);
    Route::get('enrollments/{enrollment}/movements', [EnrollmentController::class, 'movements']);

    Route::apiResource('assessment-periods', AssessmentPeriodController::class)->except(['destroy']);

    Route::apiResource('curricular-components', CurricularComponentController::class)->only(['index', 'store', 'show']);
    Route::apiResource('experience-fields', ExperienceFieldController::class)->only(['index', 'store', 'show']);
    Route::apiResource('teacher-assignments', TeacherAssignmentController::class)->except(['destroy']);

    Route::post('attendance/bulk', [AttendanceController::class, 'bulkRecord']);
    Route::get('attendance/class/{classGroupId}', [AttendanceController::class, 'byClass']);
    Route::get('attendance/student/{studentId}', [AttendanceController::class, 'byStudent']);
    Route::get('attendance/student/{studentId}/frequency', [AttendanceController::class, 'studentFrequency']);
    Route::get('attendance/alerts/{studentId}', [AttendanceController::class, 'alerts']);
    Route::post('absence-justifications', [AttendanceController::class, 'storeJustification']);
    Route::post('absence-justifications/{id}/approve', [AttendanceController::class, 'approveJustification']);
    Route::get('attendance-configs', [AttendanceController::class, 'indexConfig']);
    Route::post('attendance-configs', [AttendanceController::class, 'storeConfig']);

    Route::apiResource('lesson-records', LessonRecordController::class)->except(['destroy']);

    Route::post('grades/bulk', [AssessmentController::class, 'bulkGrades']);
    Route::get('grades', [AssessmentController::class, 'indexGrades']);
    Route::put('grades/{id}', [AssessmentController::class, 'updateGrade']);
    Route::post('grades/recovery', [AssessmentController::class, 'recoveryGrade']);
    Route::post('period-averages/calculate', [AssessmentController::class, 'calculatePeriodAverage']);
    Route::post('descriptive-reports', [AssessmentController::class, 'storeDescriptiveReport']);
    Route::get('descriptive-reports', [AssessmentController::class, 'indexDescriptiveReports']);
    Route::get('report-cards/student/{studentId}', [AssessmentController::class, 'reportCard']);
    Route::get('assessment-configs', [AssessmentController::class, 'indexConfigs']);
    Route::post('assessment-configs', [AssessmentController::class, 'storeConfig']);

    Route::get('period-closings', [PeriodClosingController::class, 'index']);
    Route::get('period-closings/dashboard', [PeriodClosingController::class, 'dashboard']);
    Route::get('period-closings/{id}', [PeriodClosingController::class, 'show']);
    Route::post('period-closings/{id}/check', [PeriodClosingController::class, 'check']);
    Route::post('period-closings/{id}/submit', [PeriodClosingController::class, 'submit']);
    Route::post('period-closings/{id}/validate', [PeriodClosingController::class, 'validate']);
    Route::post('period-closings/{id}/close', [PeriodClosingController::class, 'close']);
    Route::post('rectifications', [PeriodClosingController::class, 'storeRectification']);
    Route::post('rectifications/{id}/approve', [PeriodClosingController::class, 'approveRectification']);
    Route::post('final-results/calculate', [PeriodClosingController::class, 'calculateFinalResult']);
    Route::get('final-results/student/{studentId}', [PeriodClosingController::class, 'studentFinalResult']);
});
