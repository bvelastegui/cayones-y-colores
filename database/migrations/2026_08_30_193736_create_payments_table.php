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
        Schema::create('payments', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();
            $table->foreignId('tuition_id')->constrained('tuitions')->cascadeOnDelete();
            $table->string('payment_method');
            $table->decimal('amount_paid', 10, 2);
            $table->date('payment_date');
            $table->string('reference_number')->nullable();
            $table->timestamps();

            $table->index('tuition_id');
            $table->index('payment_method');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
