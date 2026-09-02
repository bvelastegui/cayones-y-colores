<?php

namespace App\Actions\Teachers;

use App\Models\Teacher;
use App\Services\UserAccountService;
use Illuminate\Support\Facades\DB;

class CreateTeacherAction
{
    public function __construct(private UserAccountService $userAccountService) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data): Teacher
    {
        return DB::transaction(function () use ($data): Teacher {
            $teacher = Teacher::create($data);
            $this->userAccountService->createForTeacher($teacher);

            return $teacher->load('user');
        });
    }
}
