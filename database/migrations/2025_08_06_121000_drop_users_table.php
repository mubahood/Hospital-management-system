<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop the users table since we've moved everything to admin_users
        Schema::dropIfExists('users');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Recreate the users table if we need to rollback
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enterprise_id')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->unique()->nullable();
            $table->string('phone_number_1')->nullable();
            $table->string('phone_number_2')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->enum('sex', ['male', 'female', 'other'])->nullable();
            $table->text('home_address')->nullable();
            $table->text('current_address')->nullable();
            $table->string('nationality')->nullable();
            $table->string('religion')->nullable();
            $table->string('spouse_name')->nullable();
            $table->string('spouse_phone')->nullable();
            $table->string('father_name')->nullable();
            $table->string('father_phone')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_phone')->nullable();
            $table->string('languages')->nullable();
            $table->string('emergency_person_name')->nullable();
            $table->string('emergency_person_phone')->nullable();
            $table->string('national_id_number')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('tin')->nullable();
            $table->string('nssf_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('title')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('user_type')->default('patient');
            $table->string('avatar')->nullable();
            $table->text('intro')->nullable();
            $table->decimal('rate', 8, 2)->default(0);
            $table->string('belongs_to_company')->nullable();
            $table->string('card_status')->default('inactive');
            $table->string('card_number')->nullable();
            $table->decimal('card_balance', 15, 2)->default(0);
            $table->boolean('card_accepts_credit')->default(false);
            $table->decimal('card_max_credit', 15, 2)->default(0);
            $table->boolean('card_accepts_cash')->default(true);
            $table->boolean('is_dependent')->default(false);
            $table->string('dependent_status')->nullable();
            $table->unsignedBigInteger('dependent_id')->nullable();
            $table->date('card_expiry')->nullable();
            $table->string('belongs_to_company_status')->nullable();
            $table->text('medical_history')->nullable();
            $table->text('allergies')->nullable();
            $table->text('current_medications')->nullable();
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->date('insurance_expiry_date')->nullable();
            $table->string('family_doctor_name')->nullable();
            $table->string('family_doctor_phone')->nullable();
            $table->string('employment_status')->nullable();
            $table->string('employer_name')->nullable();
            $table->decimal('annual_income', 15, 2)->nullable();
            $table->string('education_level')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('enterprise_id');
            $table->index('company_id');
            $table->index('user_type');
            $table->index('card_number');
            $table->index('dependent_id');
        });
    }
}
