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
            ['name' => 'Maternal 1', 'max_capacity' => 12, 'student_aux_ratio' => 3],
            ['name' => 'Maternal 2', 'max_capacity' => 12, 'student_aux_ratio' => 3],
            ['name' => 'Inicial 1', 'max_capacity' => 18, 'student_aux_ratio' => 6],
            ['name' => 'Inicial 2', 'max_capacity' => 18, 'student_aux_ratio' => 6],
            ['name' => '1ro de Básica', 'max_capacity' => 9, 'student_aux_ratio' => 0],
        ];

        foreach ($levels as $level) {
            Level::create($level);
        }
    }
}
