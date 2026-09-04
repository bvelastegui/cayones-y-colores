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
        Schema::create('academic_periods', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();
            $table->string('name')->unique();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->dateTime('enrollment_opens_at');
            $table->dateTime('enrollment_closes_at');
            $table->string('status')->default('draft');
            $table->dateTime('allocation_started_at')->nullable();
            $table->dateTime('allocation_completed_at')->nullable();
            $table->timestamps();

            $table->index(['enrollment_opens_at', 'enrollment_closes_at'], 'periods_enrollment_window_idx');
            $table->index(['starts_on', 'ends_on'], 'periods_dates_idx');
            $table->index('status', 'periods_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_periods');
    }
};
