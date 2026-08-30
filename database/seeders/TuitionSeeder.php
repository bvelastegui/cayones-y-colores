<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Tuition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TuitionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studentIds = Student::pluck('id')->toArray();

        foreach ($studentIds as $studentId) {
            Tuition::factory()->count(fake()->numberBetween(1, 3))->create([
                'student_id' => $studentId,
            ]);
        }
    }
}
