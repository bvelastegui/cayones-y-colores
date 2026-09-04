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
        Schema::create('enrollment_forms', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();
            $table->unsignedBigInteger('enrollment_id');
            $table->string('current_step')->default('student');
            $table->longText('draft_data')->nullable();
            $table->longText('submitted_data')->nullable();
            $table->longText('snapshot_data')->nullable();
            $table->string('privacy_policy_version')->nullable();
            $table->string('medical_consent_version')->nullable();
            $table->string('emergency_consent_version')->nullable();
            $table->dateTime('consented_at')->nullable();
            $table->ipAddress('consent_ip')->nullable();
            $table->unsignedBigInteger('consented_by')->nullable();
            $table->timestamps();

            $table->unique('enrollment_id', 'enrollment_forms_enrollment_unique');
            $table->foreign('enrollment_id', 'enrollment_forms_enrollment_fk')->references('id')->on('enrollments')->restrictOnDelete();
            $table->foreign('consented_by', 'enrollment_forms_user_fk')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('student_profiles', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('preferred_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('nationality')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('previous_institution')->nullable();
            $table->string('previous_level')->nullable();
            $table->longText('academic_background')->nullable();
            $table->longText('educational_needs')->nullable();
            $table->longText('educational_supports')->nullable();
            $table->string('languages')->nullable();
            $table->string('blood_type')->nullable();
            $table->text('pediatrician_name')->nullable();
            $table->text('pediatrician_phone')->nullable();
            $table->longText('developmental_notes')->nullable();
            $table->longText('care_instructions')->nullable();
            $table->longText('medical_observations')->nullable();
            $table->longText('additional_notes')->nullable();
            $table->timestamps();

            $table->unique('student_id', 'student_profiles_student_unique');
            $table->foreign('student_id', 'student_profiles_student_fk')->references('id')->on('students')->cascadeOnDelete();
        });

        Schema::create('student_addresses', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('country')->default('Ecuador');
            $table->string('province');
            $table->string('city');
            $table->string('parish')->nullable();
            $table->string('main_street');
            $table->string('secondary_street')->nullable();
            $table->string('house_number')->nullable();
            $table->string('reference')->nullable();
            $table->string('residence_type')->nullable();
            $table->string('housing_relationship')->nullable();
            $table->timestamps();

            $table->unique('student_id', 'student_addresses_student_unique');
            $table->foreign('student_id', 'student_addresses_student_fk')->references('id')->on('students')->cascadeOnDelete();
        });

        Schema::create('student_legal_representatives', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('relationship');
            $table->string('id_type')->default('cedula');
            $table->string('id_number');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->string('occupation')->nullable();
            $table->string('workplace')->nullable();
            $table->string('work_phone')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();

            $table->unique('student_id', 'student_legal_rep_student_unique');
            $table->foreign('student_id', 'student_legal_rep_student_fk')->references('id')->on('students')->cascadeOnDelete();
        });

        Schema::create('student_billing_profiles', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('person_type')->default('natural');
            $table->string('tax_id_type')->default('cedula');
            $table->string('tax_id');
            $table->string('business_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('address');
            $table->timestamps();

            $table->unique('student_id', 'student_billing_student_unique');
            $table->foreign('student_id', 'student_billing_student_fk')->references('id')->on('students')->cascadeOnDelete();
        });

        Schema::create('student_health_insurances', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->boolean('has_insurance')->default(false);
            $table->string('provider')->nullable();
            $table->string('policy_number')->nullable();
            $table->string('plan_name')->nullable();
            $table->string('policy_holder')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->date('expires_on')->nullable();
            $table->timestamps();

            $table->unique('student_id', 'student_insurance_student_unique');
            $table->foreign('student_id', 'student_insurance_student_fk')->references('id')->on('students')->cascadeOnDelete();
        });

        Schema::create('student_emergency_contacts', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedTinyInteger('position');
            $table->string('full_name');
            $table->string('relationship');
            $table->string('phone');
            $table->string('alternate_phone')->nullable();
            $table->string('address')->nullable();
            $table->boolean('authorized_pickup')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'position'], 'student_emergency_position_unique');
            $table->foreign('student_id', 'student_emergency_student_fk')->references('id')->on('students')->cascadeOnDelete();
        });

        Schema::create('student_medical_conditions', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->text('name');
            $table->longText('details')->nullable();
            $table->longText('care_instructions')->nullable();
            $table->timestamps();

            $table->index('student_id', 'student_conditions_student_idx');
            $table->foreign('student_id', 'student_conditions_student_fk')->references('id')->on('students')->cascadeOnDelete();
        });

        Schema::create('student_allergies', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->text('allergen');
            $table->text('severity');
            $table->longText('reaction')->nullable();
            $table->longText('response_instructions')->nullable();
            $table->timestamps();

            $table->index('student_id', 'student_allergies_student_idx');
            $table->foreign('student_id', 'student_allergies_student_fk')->references('id')->on('students')->cascadeOnDelete();
        });

        Schema::create('student_medications', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->text('name');
            $table->text('dose')->nullable();
            $table->text('schedule')->nullable();
            $table->text('prescriber')->nullable();
            $table->longText('instructions')->nullable();
            $table->timestamps();

            $table->index('student_id', 'student_medications_student_idx');
            $table->foreign('student_id', 'student_medications_student_fk')->references('id')->on('students')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_medications');
        Schema::dropIfExists('student_allergies');
        Schema::dropIfExists('student_medical_conditions');
        Schema::dropIfExists('student_emergency_contacts');
        Schema::dropIfExists('student_health_insurances');
        Schema::dropIfExists('student_billing_profiles');
        Schema::dropIfExists('student_legal_representatives');
        Schema::dropIfExists('student_addresses');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('enrollment_forms');
    }
};
