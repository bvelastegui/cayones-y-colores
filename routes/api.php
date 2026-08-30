<?php

use App\Http\Controllers\Api\AcademicReportController;
use App\Http\Controllers\Api\AdmissionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CourseTeacherController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\LevelController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\RepresentativeController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\TuitionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/user', [AuthController::class, 'user'])->name('user');
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function (): void {
    Route::apiResource('users', UserController::class);
    Route::apiResource('levels', LevelController::class);
    Route::apiResource('admissions', AdmissionController::class);
    Route::apiResource('representatives', RepresentativeController::class);
    Route::apiResource('teachers', TeacherController::class);
    Route::apiResource('courses', CourseController::class);
    Route::apiResource('course-teachers', CourseTeacherController::class);
    Route::apiResource('students', StudentController::class);
    Route::apiResource('enrollments', EnrollmentController::class);
    Route::apiResource('tuitions', TuitionController::class);
    Route::apiResource('payments', PaymentController::class);
    Route::apiResource('academic-reports', AcademicReportController::class);
});
