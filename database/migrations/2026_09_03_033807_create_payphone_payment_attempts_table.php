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
        Schema::create('payphone_payment_attempts', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();
            $table->foreignId('tuition_id')->constrained()->cascadeOnDelete();
            $table->uuid('client_transaction_id')->unique();
            $table->unsignedBigInteger('amount_in_cents');
            $table->string('status');
            $table->string('payphone_payment_id')->nullable();
            $table->text('payment_url')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable()->unique();
            $table->timestamp('expires_at');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['tuition_id', 'status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payphone_payment_attempts');
    }
};
