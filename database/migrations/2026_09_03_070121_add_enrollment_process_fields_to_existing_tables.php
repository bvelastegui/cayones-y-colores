<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('levels', function (Blueprint $table): void {
            $table->unsignedTinyInteger('sequence_order')->nullable()->unique('levels_sequence_unique');
            $table->unsignedBigInteger('next_level_id')->nullable();
            $table->foreign('next_level_id', 'levels_next_level_fk')
                ->references('id')->on('levels')->nullOnDelete();
        });

        DB::table('levels')->where('name', '1ro de Básica')->update(['name' => 'Primero EGB']);

        $levelIds = DB::table('levels')
            ->whereIn('name', ['Maternal 1', 'Maternal 2', 'Inicial 1', 'Inicial 2', 'Primero EGB'])
            ->pluck('id', 'name');

        $orderedLevels = ['Maternal 1', 'Maternal 2', 'Inicial 1', 'Inicial 2', 'Primero EGB'];

        foreach ($orderedLevels as $index => $name) {
            if (! isset($levelIds[$name])) {
                continue;
            }

            DB::table('levels')->where('id', $levelIds[$name])->update([
                'sequence_order' => $index + 1,
                'next_level_id' => isset($orderedLevels[$index + 1])
                    ? ($levelIds[$orderedLevels[$index + 1]] ?? null)
                    : null,
            ]);
        }

        Schema::table('students', function (Blueprint $table): void {
            $table->unsignedBigInteger('level_id')->nullable()->after('representative_id');
            $table->string('lifecycle_status')->default('active')->after('level_id');
            $table->foreign('level_id', 'students_level_fk')->references('id')->on('levels')->restrictOnDelete();
            $table->index('lifecycle_status', 'students_lifecycle_idx');
        });

        DB::table('students')->select('id')->orderBy('id')->chunkById(500, function ($students): void {
            foreach ($students as $student) {
                $levelId = DB::table('enrollments')
                    ->join('courses', 'courses.id', '=', 'enrollments.course_id')
                    ->where('enrollments.student_id', $student->id)
                    ->latest('enrollments.id')
                    ->value('courses.level_id');

                $levelId ??= DB::table('admissions')
                    ->where('student_id', $student->id)
                    ->where('status', 'approved')
                    ->latest('id')
                    ->value('level_id');

                DB::table('students')->where('id', $student->id)->update(['level_id' => $levelId]);
            }
        });

        Schema::table('enrollments', function (Blueprint $table): void {
            $table->dropUnique('enrollments_student_id_course_id_unique');
            $table->dropForeign('enrollments_student_id_foreign');
            $table->dropForeign('enrollments_course_id_foreign');
        });

        Schema::table('enrollments', function (Blueprint $table): void {
            $table->unsignedBigInteger('course_id')->nullable()->change();
            $table->foreign('student_id', 'enrollments_student_fk')->references('id')->on('students')->restrictOnDelete();
            $table->foreign('course_id', 'enrollments_course_fk')->references('id')->on('courses')->nullOnDelete();
            $table->unsignedBigInteger('academic_period_id')->nullable()->after('student_id');
            $table->unsignedBigInteger('level_id')->nullable()->after('academic_period_id');
            $table->string('level_outcome')->nullable()->after('status');
            $table->dateTime('form_completed_at')->nullable();
            $table->dateTime('payment_started_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('assigned_at')->nullable();
            $table->dateTime('finalized_at')->nullable();
            $table->dateTime('exception_until')->nullable();
            $table->string('assignment_issue')->nullable();

            $table->foreign('academic_period_id', 'enrollments_period_fk')->references('id')->on('academic_periods')->restrictOnDelete();
            $table->foreign('level_id', 'enrollments_level_fk')->references('id')->on('levels')->restrictOnDelete();
            $table->unique(['student_id', 'academic_period_id'], 'enrollments_student_period_unique');
            $table->index(['academic_period_id', 'status'], 'enrollments_period_status_idx');
            $table->index(['level_id', 'status'], 'enrollments_level_status_idx');
        });

        DB::table('enrollments')->select(['id', 'course_id'])->whereNotNull('course_id')
            ->orderBy('id')->chunkById(500, function ($enrollments): void {
                foreach ($enrollments as $enrollment) {
                    DB::table('enrollments')->where('id', $enrollment->id)->update([
                        'level_id' => DB::table('courses')->where('id', $enrollment->course_id)->value('level_id'),
                    ]);
                }
            });

        Schema::table('tuitions', function (Blueprint $table): void {
            $table->date('billing_period')->nullable()->change();
            $table->string('concept')->default('monthly')->after('student_id');
            $table->unsignedBigInteger('enrollment_id')->nullable()->after('student_id');
            $table->foreign('enrollment_id', 'tuitions_enrollment_fk')->references('id')->on('enrollments')->nullOnDelete();
            $table->unique('enrollment_id', 'tuitions_enrollment_unique');
            $table->index(['concept', 'status'], 'tuitions_concept_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tuitions', function (Blueprint $table): void {
            $table->dropIndex('tuitions_concept_status_idx');
            $table->dropUnique('tuitions_enrollment_unique');
            $table->dropForeign('tuitions_enrollment_fk');
            $table->dropColumn(['enrollment_id', 'concept']);
        });

        Schema::table('enrollments', function (Blueprint $table): void {
            $table->dropIndex('enrollments_level_status_idx');
            $table->dropIndex('enrollments_period_status_idx');
            $table->dropUnique('enrollments_student_period_unique');
            $table->dropForeign('enrollments_level_fk');
            $table->dropForeign('enrollments_period_fk');
            $table->dropForeign('enrollments_course_fk');
            $table->dropForeign('enrollments_student_fk');
            $table->dropColumn([
                'academic_period_id',
                'level_id',
                'level_outcome',
                'form_completed_at',
                'payment_started_at',
                'paid_at',
                'assigned_at',
                'finalized_at',
                'exception_until',
                'assignment_issue',
            ]);
        });

        Schema::table('enrollments', function (Blueprint $table): void {
            $table->unsignedBigInteger('course_id')->nullable(false)->change();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
            $table->unique(['student_id', 'course_id']);
        });

        Schema::table('students', function (Blueprint $table): void {
            $table->dropIndex('students_lifecycle_idx');
            $table->dropForeign('students_level_fk');
            $table->dropColumn(['level_id', 'lifecycle_status']);
        });

        Schema::table('levels', function (Blueprint $table): void {
            $table->dropForeign('levels_next_level_fk');
            $table->dropUnique('levels_sequence_unique');
            $table->dropColumn(['sequence_order', 'next_level_id']);
        });

        DB::table('levels')->where('name', 'Primero EGB')->update(['name' => '1ro de Básica']);
    }
};
