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
        Schema::create('enrollment_audits', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();
            $table->unsignedBigInteger('enrollment_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('event');
            $table->json('metadata')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('enrollment_id', 'enrollment_audits_enrollment_fk')->references('id')->on('enrollments')->nullOnDelete();
            $table->foreign('user_id', 'enrollment_audits_user_fk')->references('id')->on('users')->nullOnDelete();
            $table->index(['enrollment_id', 'created_at'], 'enrollment_audits_history_idx');
            $table->index(['event', 'created_at'], 'enrollment_audits_event_idx');
        });

        Schema::create('student_record_access_logs', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('scope');
            $table->string('reason')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('student_id', 'record_access_student_fk')->references('id')->on('students')->nullOnDelete();
            $table->foreign('user_id', 'record_access_user_fk')->references('id')->on('users')->nullOnDelete();
            $table->index(['student_id', 'created_at'], 'record_access_student_history_idx');
            $table->index(['user_id', 'created_at'], 'record_access_user_history_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_record_access_logs');
        Schema::dropIfExists('enrollment_audits');
    }
};
