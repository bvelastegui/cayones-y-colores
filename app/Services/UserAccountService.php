<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Events\UserAccountCreated;
use App\Models\Representative;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Str;

class UserAccountService
{
    public function createForRepresentative(Representative $representative): User
    {
        $user = $representative->user ?? User::firstOrCreate(
            ['email' => $representative->email],
            [
                'name' => "{$representative->first_name} {$representative->last_name}",
                'password' => Str::password(64),
                'identification' => $representative->id_card,
                'phone' => $representative->phone,
                'role' => UserRole::Representative,
                'is_active' => true,
            ],
        );

        $representative->update(['user_id' => $user->id]);

        if ($user->wasRecentlyCreated) {
            UserAccountCreated::dispatch($user);
        }

        return $user;
    }

    public function createForTeacher(Teacher $teacher): User
    {
        $user = $teacher->user ?? User::firstOrCreate(
            ['email' => $teacher->email],
            [
                'name' => "{$teacher->first_name} {$teacher->last_name}",
                'password' => Str::password(64),
                'identification' => $teacher->id_card ?? (string) $teacher->id,
                'role' => UserRole::Teacher,
                'is_active' => true,
            ],
        );

        $teacher->update(['user_id' => $user->id]);

        if ($user->wasRecentlyCreated) {
            UserAccountCreated::dispatch($user);
        }

        return $user;
    }
}
