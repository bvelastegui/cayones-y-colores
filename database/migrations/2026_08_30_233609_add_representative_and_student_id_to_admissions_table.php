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
        Schema::table('admissions', function (Blueprint $table): void {
            $table->foreignId('representative_id')
                ->after('status')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('student_id')
                ->after('representative_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table): void {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['representative_id']);
            $table->dropColumn(['student_id', 'representative_id']);
        });
    }
};
