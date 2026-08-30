<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            LevelSeeder::class,
            RepresentativeSeeder::class,
            TeacherSeeder::class,
            AdmissionSeeder::class,
            StudentSeeder::class,
            CourseSeeder::class,
            CourseTeacherSeeder::class,
            EnrollmentSeeder::class,
            TuitionSeeder::class,
            PaymentSeeder::class,
            AcademicReportSeeder::class,
        ]);
    }
}
