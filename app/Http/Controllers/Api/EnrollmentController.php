<?php

namespace App\Http\Controllers\Api;

use App\Enums\EnrollmentOutcome;
use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AssignEnrollmentRequest;
use App\Http\Requests\Api\CompleteEnrollmentOutcomeRequest;
use App\Http\Requests\Api\EnrollmentRequest;
use App\Http\Requests\Api\GrantEnrollmentExceptionRequest;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Level;
use App\Services\EnrollmentAllocationService;
use App\Services\EnrollmentAuditService;
use App\Services\EnrollmentOutcomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class EnrollmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $enrollments = Enrollment::with(['student', 'academicPeriod', 'level', 'course'])
            ->paginate($request->integer('per_page', 15));

        return response()->json($enrollments);
    }

    public function store(EnrollmentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $enrollment = Enrollment::create($data);

        return response()->json($enrollment->load(['student', 'course']), Response::HTTP_CREATED);
    }

    public function show(Enrollment $enrollment): JsonResponse
    {
        return response()->json($enrollment->load([
            'student', 'academicPeriod', 'level', 'course.courseTeachers.teacher', 'tuition.payments', 'audits.user',
        ]));
    }

    public function update(EnrollmentRequest $request, Enrollment $enrollment): JsonResponse
    {
        $data = $request->validated();

        $enrollment->update($data);

        return response()->json($enrollment->load(['student', 'course']));
    }

    public function destroy(Enrollment $enrollment): JsonResponse
    {
        if ($enrollment->form()->exists()) {
            throw ValidationException::withMessages([
                'enrollment' => ['Las matrículas con ficha histórica no pueden eliminarse.'],
            ]);
        }

        $enrollment->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function assign(
        AssignEnrollmentRequest $request,
        Enrollment $enrollment,
        EnrollmentAllocationService $allocationService,
    ): JsonResponse {
        $course = Course::query()->findOrFail($request->integer('course_id'));

        return response()->json($allocationService->assignManually(
            $enrollment,
            $course,
            $request->user(),
            $request->string('reason')->toString(),
            $request->ip(),
        ));
    }

    public function outcome(
        CompleteEnrollmentOutcomeRequest $request,
        Enrollment $enrollment,
        EnrollmentOutcomeService $outcomeService,
    ): JsonResponse {
        $overrideLevel = $request->filled('override_level_id')
            ? Level::query()->findOrFail($request->integer('override_level_id'))
            : null;

        return response()->json($outcomeService->complete(
            $enrollment,
            EnrollmentOutcome::from($request->string('outcome')->toString()),
            $request->user(),
            $overrideLevel,
            $request->validated('reason'),
            $request->ip(),
        ));
    }

    public function grantException(
        GrantEnrollmentExceptionRequest $request,
        Enrollment $enrollment,
        EnrollmentAuditService $auditService,
    ): JsonResponse {
        if (! in_array($enrollment->status, [EnrollmentStatus::Draft, EnrollmentStatus::PendingPayment], true)) {
            throw ValidationException::withMessages([
                'enrollment' => ['Solo una ficha pendiente puede recibir una excepción de plazo.'],
            ]);
        }

        $enrollment->update(['exception_until' => $request->validated('exception_until')]);
        $auditService->record($enrollment, $request->user(), 'enrollment_exception_granted', [
            'exception_until' => $request->validated('exception_until'),
            'reason' => $request->string('reason')->toString(),
        ], $request->ip());

        return response()->json($enrollment->fresh(['student', 'academicPeriod', 'level']));
    }
}
