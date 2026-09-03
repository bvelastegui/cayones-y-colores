<?php

namespace App\Http\Controllers\Api;

use App\Actions\Payments\PayTuitionAction;
use App\Actions\Representatives\ListAvailableCoursesAction;
use App\Actions\Representatives\ListOutstandingTuitionsAction;
use App\Actions\Representatives\ListRepresentativeStudentsAction;
use App\Actions\Representatives\ListStudentReportsAction;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\EnrollStudentRequest;
use App\Http\Requests\Api\PayTuitionRequest;
use App\Models\Course;
use App\Models\Representative;
use App\Models\Student;
use App\Models\Tuition;
use App\Services\EnrollmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

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

    public function availableCourses(
        Request $request,
        Student $student,
        ListAvailableCoursesAction $listAvailableCourses,
    ): JsonResponse {
        Gate::forUser($request->user())->authorize('manage', $student);

        return response()->json($listAvailableCourses->execute($student));
    }

    public function enroll(EnrollStudentRequest $request, EnrollmentService $enrollmentService): JsonResponse
    {
        $data = $request->validated();

        $student = Student::query()->findOrFail($request->integer('student_id'));
        $course = Course::query()->findOrFail($request->integer('course_id'));

        Gate::forUser($request->user())->authorize('manage', $student);

        $enrollment = $enrollmentService->enroll($student, $course, $request->user());

        return response()->json($enrollment, Response::HTTP_CREATED);
    }

    public function tuitions(Request $request, ListOutstandingTuitionsAction $listTuitions): JsonResponse
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
