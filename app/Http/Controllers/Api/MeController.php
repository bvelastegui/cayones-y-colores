<?php

namespace App\Http\Controllers\Api;

use App\Actions\Payments\PayTuitionAction;
use App\Actions\Representatives\ListRepresentativeStudentsAction;
use App\Actions\Representatives\ListRepresentativeTuitionsAction;
use App\Actions\Representatives\ListStudentReportsAction;
use App\Enums\EnrollmentStatus;
use App\Enums\PaymentMethod;
use App\Enums\PayphonePaymentStatus;
use App\Enums\TuitionConcept;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CompleteEnrollmentRequest;
use App\Http\Requests\Api\PayTuitionRequest;
use App\Http\Requests\Api\SaveEnrollmentSectionRequest;
use App\Models\Enrollment;
use App\Models\Representative;
use App\Models\Student;
use App\Models\Tuition;
use App\Services\EnrollmentFormService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class MeController extends Controller
{
    public function students(Request $request, ListRepresentativeStudentsAction $listStudents): JsonResponse
    {
        $representative = $this->representative($request);

        if (! $representative) {
            return response()->json([]);
        }

        return response()->json($listStudents->execute($representative));
    }

    public function enrollment(
        Request $request,
        Student $student,
        EnrollmentFormService $enrollmentFormService,
    ): JsonResponse {
        Gate::forUser($request->user())->authorize('manage', $student);

        $enrollment = $student->enrollments()->whereNotNull('academic_period_id')->latest('id')->first();
        $enrollment ??= $student->enrollments()->where('status', EnrollmentStatus::Active)->latest('id')->first();

        return response()->json(
            $enrollment instanceof Enrollment ? $enrollmentFormService->present($enrollment) : null,
        );
    }

    public function startEnrollment(
        Request $request,
        Student $student,
        EnrollmentFormService $enrollmentFormService,
    ): JsonResponse {
        Gate::forUser($request->user())->authorize('manage', $student);

        $enrollment = $enrollmentFormService->startOrResume($student, $request->user(), $request->ip());

        return response()->json($enrollment, Response::HTTP_CREATED);
    }

    public function showEnrollment(
        Request $request,
        Enrollment $enrollment,
        EnrollmentFormService $enrollmentFormService,
    ): JsonResponse {
        Gate::forUser($request->user())->authorize('manage', $enrollment->student);

        return response()->json($enrollmentFormService->present($enrollment));
    }

    public function saveEnrollmentSection(
        SaveEnrollmentSectionRequest $request,
        Enrollment $enrollment,
        string $section,
        EnrollmentFormService $enrollmentFormService,
    ): JsonResponse {
        return response()->json($enrollmentFormService->saveSection(
            $enrollment,
            $section,
            $request->validated(),
            $request->user(),
            $request->ip(),
        ));
    }

    public function completeEnrollment(
        CompleteEnrollmentRequest $request,
        Enrollment $enrollment,
        EnrollmentFormService $enrollmentFormService,
    ): JsonResponse {
        return response()->json($enrollmentFormService->complete(
            $enrollment,
            $request->user(),
            $request->ip(),
        ));
    }

    public function payEnrollment(
        Request $request,
        Enrollment $enrollment,
        PayTuitionAction $payTuition,
    ): JsonResponse {
        Gate::forUser($request->user())->authorize('manage', $enrollment->student);

        if (! in_array($enrollment->status, [EnrollmentStatus::PendingPayment, EnrollmentStatus::PaymentInProgress], true)) {
            throw ValidationException::withMessages([
                'enrollment' => ['La matrícula no se encuentra pendiente de pago.'],
            ]);
        }

        $tuition = $enrollment->tuition()->firstOrFail();
        $hasReusableAttempt = $tuition->payphonePaymentAttempts()
            ->where('status', PayphonePaymentStatus::Prepared)
            ->where('expires_at', '>', now())
            ->exists();

        if (! $enrollment->academicPeriod->acceptsEnrollmentChanges($enrollment->exception_until)
            && ! $hasReusableAttempt) {
            throw ValidationException::withMessages([
                'period' => ['El periodo de matrículas cerró y ya no admite nuevos intentos de pago.'],
            ]);
        }

        $paymentPreparation = $payTuition->execute(
            new Collection([$tuition]),
            PaymentMethod::Payphone,
            TuitionConcept::Enrollment,
            $request->user(),
        );

        return response()->json(['payment_url' => $paymentPreparation->paymentUrl]);
    }

    public function tuitions(Request $request, ListRepresentativeTuitionsAction $listTuitions): JsonResponse
    {
        $representative = $this->representative($request);

        if (! $representative) {
            return response()->json([]);
        }

        return response()->json($listTuitions->execute($representative));
    }

    public function reports(
        Request $request,
        Student $student,
        ListStudentReportsAction $listReports,
    ): JsonResponse {
        Gate::forUser($request->user())->authorize('manage', $student);

        return response()->json($listReports->execute($student));
    }

    public function payWithPayphone(PayTuitionRequest $request, PayTuitionAction $payTuition): JsonResponse
    {
        $tuitionIds = $request->collect('tuition_ids')->map(fn (mixed $id): int => (int) $id);
        $tuitions = Tuition::query()->whereKey($tuitionIds)->orderBy('id')->get();

        foreach ($tuitions as $tuition) {
            Gate::forUser($request->user())->authorize('manage', $tuition->student);
        }

        $paymentPreparation = $payTuition->execute($tuitions, PaymentMethod::Payphone);

        return response()->json([
            'payment_url' => $paymentPreparation->paymentUrl,
        ]);
    }

    private function representative(Request $request): ?Representative
    {
        return $request->user()->representative;
    }
}
