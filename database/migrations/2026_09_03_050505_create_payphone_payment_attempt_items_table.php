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
        Schema::create('payphone_payment_attempt_items', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();
            $table->foreignId('payphone_payment_attempt_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('tuition_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount_in_cents');
            $table->timestamps();

            $table->unique(['payphone_payment_attempt_id', 'tuition_id']);
        });

        DB::table('payphone_payment_attempt_items')->insertUsing(
            ['payphone_payment_attempt_id', 'tuition_id', 'amount_in_cents'],
            DB::table('payphone_payment_attempts')->select(['id', 'tuition_id', 'amount_in_cents']),
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payphone_payment_attempt_items');
    }
};
