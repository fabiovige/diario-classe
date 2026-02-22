<?php

use App\Modules\AcademicCalendar\Presentation\Controllers\AssessmentPeriodController;
use App\Modules\Assessment\Presentation\Controllers\AssessmentController;
use App\Modules\Attendance\Presentation\Controllers\AttendanceController;
use App\Modules\PeriodClosing\Presentation\Controllers\PeriodClosingController;
use App\Modules\ClassRecord\Presentation\Controllers\LessonRecordController;
use App\Modules\Curriculum\Presentation\Controllers\CurricularComponentController;
use App\Modules\Curriculum\Presentation\Controllers\ExperienceFieldController;
use App\Modules\Curriculum\Presentation\Controllers\DailyClassController;
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

Route::middleware(['auth:sanctum', 'school.scope'])->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);

    Route::apiResource('schools', SchoolController::class);
    Route::apiResource('academic-years', AcademicYearController::class);
    Route::apiResource('shifts', ShiftController::class);
    Route::apiResource('grade-levels', GradeLevelController::class);
    Route::apiResource('class-groups', ClassGroupController::class);

    Route::apiResource('students', StudentController::class);
    Route::post('students/{student}/guardians', [StudentController::class, 'attachGuardian']);
    Route::apiResource('guardians', GuardianController::class);
    Route::apiResource('teachers', TeacherController::class);

    Route::apiResource('enrollments', EnrollmentController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::patch('enrollments/{enrollment}/reactivate', [EnrollmentController::class, 'reactivate']);
    Route::post('enrollments/{enrollment}/assign-class', [EnrollmentController::class, 'assignToClass']);
    Route::put('enrollments/{enrollment}/class-assignments/{classAssignment}', [EnrollmentController::class, 'updateClassAssignment']);
    Route::delete('enrollments/{enrollment}/class-assignments/{classAssignment}', [EnrollmentController::class, 'destroyClassAssignment']);
    Route::post('enrollments/{enrollment}/transfer', [EnrollmentController::class, 'transfer']);
    Route::get('enrollments/{enrollment}/movements', [EnrollmentController::class, 'movements']);
    Route::get('enrollments/{enrollment}/documents', [EnrollmentController::class, 'documents']);
    Route::post('enrollments/{enrollment}/documents/upload', [EnrollmentController::class, 'uploadDocument']);
    Route::get('enrollments/{enrollment}/documents/{documentType}/download', [EnrollmentController::class, 'downloadDocument']);
    Route::patch('enrollments/{enrollment}/documents/{documentType}/review', [EnrollmentController::class, 'reviewDocument']);

    Route::apiResource('assessment-periods', AssessmentPeriodController::class);

    Route::apiResource('curricular-components', CurricularComponentController::class);
    Route::apiResource('experience-fields', ExperienceFieldController::class);
    Route::get('teacher-assignments/daily-summary', [DailyClassController::class, 'index']);
    Route::apiResource('teacher-assignments', TeacherAssignmentController::class);

    Route::post('attendance/bulk', [AttendanceController::class, 'bulkRecord']);
    Route::get('attendance/class/{classGroupId}', [AttendanceController::class, 'byClass']);
    Route::get('attendance/student/{studentId}', [AttendanceController::class, 'byStudent']);
    Route::get('attendance/student/{studentId}/frequency', [AttendanceController::class, 'studentFrequency']);
    Route::get('attendance/alerts/{studentId}', [AttendanceController::class, 'alerts']);
    Route::post('absence-justifications', [AttendanceController::class, 'storeJustification']);
    Route::post('absence-justifications/{id}/approve', [AttendanceController::class, 'approveJustification']);
    Route::get('attendance-configs', [AttendanceController::class, 'indexConfig']);
    Route::post('attendance-configs', [AttendanceController::class, 'storeConfig']);

    Route::apiResource('lesson-records', LessonRecordController::class);

    Route::post('grades/bulk', [AssessmentController::class, 'bulkGrades']);
    Route::get('grades', [AssessmentController::class, 'indexGrades']);
    Route::put('grades/{id}', [AssessmentController::class, 'updateGrade']);
    Route::post('grades/recovery', [AssessmentController::class, 'recoveryGrade']);
    Route::post('period-averages/calculate', [AssessmentController::class, 'calculatePeriodAverage']);
    Route::post('descriptive-reports', [AssessmentController::class, 'storeDescriptiveReport']);
    Route::get('descriptive-reports', [AssessmentController::class, 'indexDescriptiveReports']);
    Route::get('descriptive-reports/{id}', [AssessmentController::class, 'showDescriptiveReport']);
    Route::put('descriptive-reports/{id}', [AssessmentController::class, 'updateDescriptiveReport']);
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
