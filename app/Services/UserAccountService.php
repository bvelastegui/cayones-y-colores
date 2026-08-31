<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Mail\AccountCreated;
use App\Models\Representative;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserAccountService
{
    public function createForRepresentative(Representative $representative): User
    {
        $password = $this->generatePassword();

        $user = User::create([
            'name' => "{$representative->first_name} {$representative->last_name}",
            'email' => $representative->email,
            'password' => Hash::make($password),
            'identification' => $representative->id_card,
            'phone' => $representative->phone,
            'role' => UserRole::Representative,
            'is_active' => true,
        ]);

        $representative->update(['user_id' => $user->id]);

        $this->notify($user, $password, 'Representante');

        return $user;
    }

    public function createForTeacher(Teacher $teacher): User
    {
        $password = $this->generatePassword();

        $user = User::create([
            'name' => "{$teacher->first_name} {$teacher->last_name}",
            'email' => $teacher->email,
            'password' => Hash::make($password),
            'identification' => $teacher->id_card ?? $teacher->id,
            'role' => UserRole::Teacher,
            'is_active' => true,
        ]);

        $teacher->update(['user_id' => $user->id]);

        $this->notify($user, $password, 'Docente');

        return $user;
    }

    private function generatePassword(): string
    {
        return Str::password(12, true, true, false, false);
    }

    private function notify(User $user, string $password, string $roleLabel): void
    {
        Mail::to($user->email)->send(new AccountCreated(
            $user->name,
            $user->email,
            $password,
            $roleLabel,
        ));
    }
}
