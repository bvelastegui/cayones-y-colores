<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            ['name' => 'Maternal 1', 'max_capacity' => 12, 'student_aux_ratio' => 3, 'enrollment_fee' => 150.00, 'monthly_fee' => 180.00],
            ['name' => 'Maternal 2', 'max_capacity' => 12, 'student_aux_ratio' => 3, 'enrollment_fee' => 150.00, 'monthly_fee' => 180.00],
            ['name' => 'Inicial 1', 'max_capacity' => 18, 'student_aux_ratio' => 6, 'enrollment_fee' => 170.00, 'monthly_fee' => 200.00],
            ['name' => 'Inicial 2', 'max_capacity' => 18, 'student_aux_ratio' => 6, 'enrollment_fee' => 170.00, 'monthly_fee' => 200.00],
            ['name' => '1ro de Básica', 'max_capacity' => 9, 'student_aux_ratio' => 0, 'enrollment_fee' => 200.00, 'monthly_fee' => 230.00],
        ];

        foreach ($levels as $level) {
            Level::create($level);
        }
    }
}
