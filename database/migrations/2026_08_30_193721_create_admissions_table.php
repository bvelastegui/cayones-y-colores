<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admissions', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();
            $table->foreignId('level_id')->constrained('levels');
            $table->string('applicant_first_name');
            $table->string('applicant_last_name');
            $table->date('applicant_birth_date');
            $table->string('representative_names');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->string('status')->default('pending');
            $table->date('application_date');
            $table->timestamps();

            $table->index('level_id');
            $table->index('status');
            $table->index('application_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
