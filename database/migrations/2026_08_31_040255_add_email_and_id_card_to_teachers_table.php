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
        Schema::table('teachers', function (Blueprint $table): void {
            $table->string('id_card')->after('id')->nullable()->unique();
            $table->string('email')->after('last_name')->nullable()->unique();
            $table->index('id_card');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table): void {
            $table->dropIndex(['id_card']);
            $table->dropIndex(['email']);
            $table->dropColumn(['id_card', 'email']);
        });
    }
};
