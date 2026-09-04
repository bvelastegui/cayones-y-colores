<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AcademicPeriodRequest;
use App\Http\Requests\Api\RunEnrollmentAssignmentRequest;
use App\Models\AcademicPeriod;
use App\Services\EnrollmentAllocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcademicPeriodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            AcademicPeriod::query()->withCount('enrollments')->latest('starts_on')
                ->paginate($request->integer('per_page', 15)),
        );
    }

    public function store(AcademicPeriodRequest $request): JsonResponse
    {
        $data = $request->validated();
        $academicPeriod = DB::transaction(function () use ($data): AcademicPeriod {
            AcademicPeriod::query()->lockForUpdate()->get();
            $this->ensureNoOverlap($data);

            return AcademicPeriod::create($data);
        });

        return response()->json($academicPeriod, Response::HTTP_CREATED);
    }

    public function show(AcademicPeriod $academicPeriod): JsonResponse
    {
        return response()->json($academicPeriod->loadCount('enrollments'));
    }

    public function update(AcademicPeriodRequest $request, AcademicPeriod $academicPeriod): JsonResponse
    {
        $data = array_merge($academicPeriod->only([
            'name', 'starts_on', 'ends_on', 'enrollment_opens_at', 'enrollment_closes_at', 'status',
        ]), $request->validated());
        DB::transaction(function () use ($data, $academicPeriod, $request): void {
            AcademicPeriod::query()->lockForUpdate()->get();
            $this->ensureNoOverlap($data, $academicPeriod);
            $academicPeriod->update($request->validated());
        });

        return response()->json($academicPeriod->fresh());
    }

    public function destroy(AcademicPeriod $academicPeriod): JsonResponse
    {
        if ($academicPeriod->enrollments()->exists()) {
            throw ValidationException::withMessages([
                'period' => ['No se puede eliminar un periodo que ya tiene matrículas.'],
            ]);
        }

        $academicPeriod->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function runAssignments(
        RunEnrollmentAssignmentRequest $request,
        AcademicPeriod $academicPeriod,
        EnrollmentAllocationService $allocationService,
    ): JsonResponse {
        return response()->json($allocationService->allocatePeriod(
            $academicPeriod,
            $request->user(),
            $request->boolean('force'),
            $request->validated('reason'),
            $request->ip(),
        ));
    }

    /** @param array<string, mixed> $data */
    private function ensureNoOverlap(array $data, ?AcademicPeriod $current = null): void
    {
        $baseQuery = AcademicPeriod::query()
            ->when($current, fn ($query) => $query->where('id', '!=', $current->id));

        $academicDatesOverlap = (clone $baseQuery)
            ->where('starts_on', '<=', $data['ends_on'])
            ->where('ends_on', '>=', $data['starts_on'])
            ->exists();

        $enrollmentWindowsOverlap = (clone $baseQuery)
            ->where('enrollment_opens_at', '<=', $data['enrollment_closes_at'])
            ->where('enrollment_closes_at', '>=', $data['enrollment_opens_at'])
            ->exists();

        if ($academicDatesOverlap || $enrollmentWindowsOverlap) {
            throw ValidationException::withMessages([
                'starts_on' => ['Los periodos académicos y sus ventanas de matrícula no pueden solaparse.'],
            ]);
        }
    }
}
