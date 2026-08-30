<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Teacher::factory()->count(8)->create()->each(function (Teacher $teacher): void {
            $user = User::factory()->teacher()->create([
                'name' => "{$teacher->first_name} {$teacher->last_name}",
                'email' => fake()->unique()->safeEmail(),
                'identification' => fake()->unique()->numerify('##########'),
            ]);

            $teacher->update(['user_id' => $user->id]);
        });
    }
}
