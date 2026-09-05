<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('protected API routes return 401 without authentication', function (string $method, string $uri) {
    $this->json($method, $uri)->assertUnauthorized();
})->with([
    'logout' => ['POST', '/api/logout'],
    'current user' => ['GET', '/api/user'],
    'store push subscription' => ['POST', '/api/push/subscriptions'],
    'delete push subscription' => ['DELETE', '/api/push/subscriptions'],
    'notifications' => ['GET', '/api/notifications'],
    'read notifications' => ['POST', '/api/notifications/read'],
    'representative students' => ['GET', '/api/me/students'],
    'representative student enrollment' => ['GET', '/api/me/students/999999/enrollment'],
    'representative starts enrollment' => ['POST', '/api/me/students/999999/enrollments'],
    'representative enrollment detail' => ['GET', '/api/me/enrollments/999999'],
    'representative saves enrollment form' => ['PUT', '/api/me/enrollments/999999/form/student'],
    'representative completes enrollment' => ['POST', '/api/me/enrollments/999999/complete'],
    'representative pays enrollment' => ['POST', '/api/me/enrollments/999999/payphone'],
    'representative tuitions' => ['GET', '/api/me/tuitions'],
    'representative reports' => ['GET', '/api/me/students/999999/reports'],
    'representative pays tuition' => ['POST', '/api/me/payments/payphone'],
    'representative payment receipt' => ['GET', '/api/me/payments/999999/receipt'],
    'teacher courses' => ['GET', '/api/teacher/courses'],
    'teacher course students' => ['GET', '/api/teacher/courses/999999/students'],
    'teacher reports' => ['GET', '/api/teacher/reports'],
    'teacher stores report' => ['POST', '/api/teacher/reports'],
    'teacher care profile' => ['GET', '/api/teacher/students/999999/care-profile'],
    'admin dashboard' => ['GET', '/api/admin/dashboard'],
    'admin users' => ['GET', '/api/users'],
    'admin levels' => ['GET', '/api/levels/999999'],
    'admin admissions' => ['GET', '/api/admissions'],
    'admin representatives' => ['GET', '/api/representatives'],
    'admin teachers' => ['GET', '/api/teachers'],
    'admin courses' => ['GET', '/api/courses'],
    'admin course teachers' => ['GET', '/api/course-teachers'],
    'admin students' => ['GET', '/api/students'],
    'admin enrollments' => ['GET', '/api/enrollments'],
    'admin academic periods' => ['GET', '/api/academic-periods'],
    'admin tuitions' => ['GET', '/api/tuitions'],
    'admin payments' => ['GET', '/api/payments'],
    'admin academic reports' => ['GET', '/api/academic-reports'],
]);

test('administrative route families return 403 to representatives', function (string $method, string $uri) {
    Sanctum::actingAs(User::factory()->representative()->create());

    $this->json($method, $uri)->assertForbidden();
})->with([
    'dashboard' => ['GET', '/api/admin/dashboard'],
    'users' => ['GET', '/api/users'],
    'levels' => ['POST', '/api/levels'],
    'admissions' => ['GET', '/api/admissions'],
    'representatives' => ['GET', '/api/representatives'],
    'teachers' => ['GET', '/api/teachers'],
    'courses' => ['GET', '/api/courses'],
    'course teachers' => ['GET', '/api/course-teachers'],
    'students' => ['GET', '/api/students'],
    'enrollments' => ['GET', '/api/enrollments'],
    'academic periods' => ['GET', '/api/academic-periods'],
    'tuitions' => ['GET', '/api/tuitions'],
    'payments' => ['GET', '/api/payments'],
    'academic reports' => ['GET', '/api/academic-reports'],
]);

test('role specific route families return 403 to another authenticated role', function (User $user, string $uri) {
    Sanctum::actingAs($user);

    $this->getJson($uri)->assertForbidden();
})->with([
    'representative route rejects teacher' => fn () => [User::factory()->teacher()->create(), '/api/me/students'],
    'teacher route rejects representative' => fn () => [User::factory()->representative()->create(), '/api/teacher/courses'],
]);
