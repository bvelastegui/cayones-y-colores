<?php

use Carbon\Carbon;
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
        $periods = [];

        foreach (DB::table('tuitions')->select(['id', 'student_id', 'generation_date'])->orderBy('id')->lazyById() as $tuition) {
            $billingPeriod = Carbon::parse($tuition->generation_date)->startOfMonth()->toDateString();
            $key = $tuition->student_id.'|'.$billingPeriod;

            if (isset($periods[$key])) {
                throw new RuntimeException(
                    "Existen pensiones duplicadas para el estudiante {$tuition->student_id} en {$billingPeriod}.",
                );
            }

            $periods[$key] = true;
        }

        Schema::table('tuitions', function (Blueprint $table) {
            $table->date('billing_period')->nullable()->after('generation_date');
        });

        DB::table('tuitions')->select(['id', 'generation_date'])->orderBy('id')->chunkById(500, function ($tuitions): void {
            foreach ($tuitions as $tuition) {
                DB::table('tuitions')->where('id', $tuition->id)->update([
                    'billing_period' => Carbon::parse($tuition->generation_date)->startOfMonth()->toDateString(),
                ]);
            }
        });

        Schema::table('tuitions', function (Blueprint $table) {
            $table->date('billing_period')->nullable(false)->change();
            $table->unique(['student_id', 'billing_period'], 'tuitions_student_billing_period_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tuitions', function (Blueprint $table) {
            $table->dropUnique('tuitions_student_billing_period_unique');
            $table->dropColumn('billing_period');
        });
    }
};
