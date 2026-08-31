<?php

namespace App\Http\Controllers\Api;

use App\Enums\TeacherType;
use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Services\UserAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teachers = Teacher::with('courses')->paginate($request->integer('per_page', 15));

        return response()->json($teachers);
    }

    public function store(Request $request, UserAccountService $userAccountService): JsonResponse
    {
        $data = $request->validate([
            'id_card' => ['required', 'string', 'max:50', 'unique:teachers'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:teachers', 'unique:users'],
            'teacher_type' => ['required', 'string', Rule::enum(TeacherType::class)],
        ]);

        $teacher = Teacher::create($data);
        $userAccountService->createForTeacher($teacher);

        return response()->json($teacher->load('user'), Response::HTTP_CREATED);
    }

    public function show(Teacher $teacher): JsonResponse
    {
        return response()->json($teacher->load(['courses', 'academicReports']));
    }

    public function update(Request $request, Teacher $teacher): JsonResponse
    {
        $data = $request->validate([
            'id_card' => ['sometimes', 'required', 'string', 'max:50', 'unique:teachers,id_card,'.$teacher->id],
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name' => ['sometimes', 'required', 'string', 'max:100'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:teachers,email,'.$teacher->id],
            'teacher_type' => ['sometimes', 'required', 'string', Rule::enum(TeacherType::class)],
        ]);

        $teacher->update($data);

        if ($teacher->user) {
            $teacher->user->update([
                'name' => "{$teacher->first_name} {$teacher->last_name}",
                'email' => $teacher->email,
                'identification' => $teacher->id_card,
            ]);
        }

        return response()->json($teacher->load('user'));
    }

    public function destroy(Teacher $teacher): JsonResponse
    {
        $teacher->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
