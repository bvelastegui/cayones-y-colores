<?php

use App\Http\Controllers\Api\AcademicReportController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdmissionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CourseTeacherController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\LevelController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PushSubscriptionController;
use App\Http\Controllers\Api\RepresentativeController;
use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\TeacherDashboardController;
use App\Http\Controllers\Api\TuitionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/reset-password', ResetPasswordController::class)->name('password.reset');
Route::get('/vapid-public-key', fn () => response()->json(['public_key' => config('services.webpush.public_key')]))->name('vapid.public-key');

Route::get('/levels', [LevelController::class, 'index'])->name('levels.index');
Route::post('/admissions', [AdmissionController::class, 'store'])->name('admissions.store');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/user', [AuthController::class, 'user'])->name('user');
    Route::post('/push/subscriptions', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::delete('/push/subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');
});

Route::middleware(['auth:sanctum', 'role:representative'])->prefix('me')->group(function (): void {
    Route::get('/students', [MeController::class, 'students'])->name('me.students');
    Route::get('/students/{student}/courses', [MeController::class, 'availableCourses'])->name('me.courses');
    Route::post('/enrollments', [MeController::class, 'enroll'])->name('me.enroll');
    Route::get('/tuitions', [MeController::class, 'tuitions'])->name('me.tuitions');
    Route::get('/students/{student}/reports', [MeController::class, 'reports'])->name('me.reports');
    Route::post('/payments/payphone', [MeController::class, 'payWithPayphone'])->name('me.payments.payphone');
});

Route::middleware(['auth:sanctum', 'role:teacher'])->prefix('teacher')->group(function (): void {
    Route::get('/courses', [TeacherDashboardController::class, 'courses'])->name('teacher.courses');
    Route::get('/courses/{course}/students', [TeacherDashboardController::class, 'students'])->name('teacher.students');
    Route::get('/reports', [TeacherDashboardController::class, 'reports'])->name('teacher.reports');
    Route::post('/reports', [TeacherDashboardController::class, 'storeReport'])->name('teacher.reports.store');
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function (): void {
    Route::get('/admin/dashboard', AdminDashboardController::class)->name('admin.dashboard');
    Route::apiResource('users', UserController::class);
    Route::apiResource('levels', LevelController::class)->except(['index']);
    Route::apiResource('admissions', AdmissionController::class)->except(['store']);
    Route::post('/admissions/{admission}/approve', [AdmissionController::class, 'approve'])->name('admissions.approve');
    Route::post('/admissions/{admission}/reject', [AdmissionController::class, 'reject'])->name('admissions.reject');
    Route::apiResource('representatives', RepresentativeController::class);
    Route::apiResource('teachers', TeacherController::class);
    Route::apiResource('courses', CourseController::class);
    Route::apiResource('course-teachers', CourseTeacherController::class);
    Route::apiResource('students', StudentController::class);
    Route::apiResource('enrollments', EnrollmentController::class);
    Route::apiResource('tuitions', TuitionController::class);
    Route::post('/tuitions/generate', [TuitionController::class, 'generate'])->name('tuitions.generate');
    Route::apiResource('payments', PaymentController::class);
    Route::apiResource('academic-reports', AcademicReportController::class);
});
