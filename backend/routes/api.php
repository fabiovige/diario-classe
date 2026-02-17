<?php

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
    Route::apiResource('shifts', ShiftController::class)->except(['update']);
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
});
