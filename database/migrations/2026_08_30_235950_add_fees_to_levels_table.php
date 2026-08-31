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
        Schema::table('levels', function (Blueprint $table): void {
            $table->decimal('enrollment_fee', 10, 2)
                ->after('student_aux_ratio')
                ->default(0);

            $table->decimal('monthly_fee', 10, 2)
                ->after('enrollment_fee')
                ->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('levels', function (Blueprint $table): void {
            $table->dropColumn(['enrollment_fee', 'monthly_fee']);
        });
    }
};
