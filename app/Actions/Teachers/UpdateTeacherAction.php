<?php

namespace App\Actions\Teachers;

use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class UpdateTeacherAction
{
    /** @param array<string, mixed> $data */
    public function execute(Teacher $teacher, array $data): Teacher
    {
        return DB::transaction(function () use ($teacher, $data): Teacher {
            $teacher->update($data);
            $teacher->user?->update([
                'name' => "{$teacher->first_name} {$teacher->last_name}",
                'email' => $teacher->email,
                'identification' => $teacher->id_card,
            ]);

            return $teacher->load('user');
        });
    }
}
