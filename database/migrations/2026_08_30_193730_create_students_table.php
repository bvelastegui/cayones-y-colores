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
        Schema::create('students', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();
            $table->foreignId('representative_id')->constrained('representatives');
            $table->string('id_card')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date');
            $table->timestamps();

            $table->index('representative_id');
            $table->index('id_card');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
