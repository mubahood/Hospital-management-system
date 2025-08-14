<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateUsersToAdminUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add all the columns from users table to admin_users
        Schema::table('admin_users', function (Blueprint $table) {
            $table->unsignedBigInteger('enterprise_id')->nullable()->after('id');
            $table->string('email')->unique()->nullable()->after('name');
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('first_name')->nullable()->after('password');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone_number_1')->nullable()->after('last_name');
            $table->string('phone_number_2')->nullable()->after('phone_number_1');
            $table->date('date_of_birth')->nullable()->after('phone_number_2');
            $table->string('place_of_birth')->nullable()->after('date_of_birth');
            $table->enum('sex', ['male', 'female', 'other'])->nullable()->after('place_of_birth');
            $table->text('home_address')->nullable()->after('sex');
            $table->text('current_address')->nullable()->after('home_address');
            $table->string('nationality')->nullable()->after('current_address');
            $table->string('religion')->nullable()->after('nationality');
            $table->string('spouse_name')->nullable()->after('religion');
            $table->string('spouse_phone')->nullable()->after('spouse_name');
            $table->string('father_name')->nullable()->after('spouse_phone');
            $table->string('father_phone')->nullable()->after('father_name');
            $table->string('mother_name')->nullable()->after('father_phone');
            $table->string('mother_phone')->nullable()->after('mother_name');
            $table->string('languages')->nullable()->after('mother_phone');
            $table->string('emergency_person_name')->nullable()->after('languages');
            $table->string('emergency_person_phone')->nullable()->after('emergency_person_name');
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_person_phone');
            $table->string('national_id_number')->nullable()->after('emergency_contact_relationship');
            $table->string('passport_number')->nullable()->after('national_id_number');
            $table->string('tin')->nullable()->after('passport_number');
            $table->string('nssf_number')->nullable()->after('tin');
            $table->string('bank_name')->nullable()->after('nssf_number');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('marital_status')->nullable()->after('bank_account_number');
            $table->string('title')->nullable()->after('marital_status');
            $table->unsignedBigInteger('company_id')->nullable()->after('title');
            $table->string('user_type')->default('patient')->after('company_id');
            $table->string('patient_status')->nullable()->after('user_type');
            $table->text('intro')->nullable()->after('avatar');
            $table->decimal('rate', 8, 2)->default(0)->after('intro');
            $table->string('belongs_to_company')->nullable()->after('rate');
            $table->string('card_status')->default('inactive')->after('belongs_to_company');
            $table->string('card_number')->nullable()->after('card_status');
            $table->decimal('card_balance', 15, 2)->default(0)->after('card_number');
            $table->boolean('card_accepts_credit')->default(false)->after('card_balance');
            $table->decimal('card_max_credit', 15, 2)->default(0)->after('card_accepts_credit');
            $table->boolean('card_accepts_cash')->default(true)->after('card_max_credit');
            $table->boolean('is_dependent')->default(false)->after('card_accepts_cash');
            $table->string('dependent_status')->nullable()->after('is_dependent');
            $table->unsignedBigInteger('dependent_id')->nullable()->after('dependent_status');
            $table->date('card_expiry')->nullable()->after('dependent_id');
            $table->string('belongs_to_company_status')->nullable()->after('card_expiry');
            
            // Medical fields
            $table->text('medical_history')->nullable()->after('belongs_to_company_status');
            $table->text('allergies')->nullable()->after('medical_history');
            $table->text('current_medications')->nullable()->after('allergies');
            $table->string('blood_type')->nullable()->after('current_medications');
            $table->decimal('height', 5, 2)->nullable()->after('blood_type');
            $table->decimal('weight', 5, 2)->nullable()->after('height');
            $table->string('insurance_provider')->nullable()->after('weight');
            $table->string('insurance_policy_number')->nullable()->after('insurance_provider');
            $table->date('insurance_expiry_date')->nullable()->after('insurance_policy_number');
            $table->string('family_doctor_name')->nullable()->after('insurance_expiry_date');
            $table->string('family_doctor_phone')->nullable()->after('family_doctor_name');
            $table->string('employment_status')->nullable()->after('family_doctor_phone');
            $table->string('employer_name')->nullable()->after('employment_status');
            $table->decimal('annual_income', 15, 2)->nullable()->after('employer_name');
            $table->string('education_level')->nullable()->after('annual_income');
            $table->string('preferred_language')->nullable()->after('education_level');
            
            // Indexes for better performance
            $table->index('enterprise_id', 'admin_users_enterprise_id_index');
            $table->index('company_id', 'admin_users_company_id_index');
            $table->index('user_type', 'admin_users_user_type_index');
            $table->index('card_number', 'admin_users_card_number_index');
            $table->index('dependent_id', 'admin_users_dependent_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            // Drop all the added columns
            $table->dropColumn([
                'enterprise_id', 'email', 'email_verified_at', 'first_name', 'last_name',
                'phone_number_1', 'phone_number_2', 'date_of_birth', 'place_of_birth',
                'sex', 'home_address', 'current_address', 'nationality', 'religion',
                'spouse_name', 'spouse_phone', 'father_name', 'father_phone',
                'mother_name', 'mother_phone', 'languages', 'emergency_person_name',
                'emergency_person_phone', 'emergency_contact_relationship', 'national_id_number',
                'passport_number', 'tin', 'nssf_number', 'bank_name', 'bank_account_number',
                'marital_status', 'title', 'company_id', 'user_type', 'patient_status',
                'intro', 'rate', 'belongs_to_company', 'card_status', 'card_number',
                'card_balance', 'card_accepts_credit', 'card_max_credit', 'card_accepts_cash',
                'is_dependent', 'dependent_status', 'dependent_id', 'card_expiry',
                'belongs_to_company_status', 'medical_history', 'allergies', 'current_medications',
                'blood_type', 'height', 'weight', 'insurance_provider', 'insurance_policy_number',
                'insurance_expiry_date', 'family_doctor_name', 'family_doctor_phone',
                'employment_status', 'employer_name', 'annual_income', 'education_level',
                'preferred_language'
            ]);
            
            // Drop indexes
            $table->dropIndex('admin_users_enterprise_id_index');
            $table->dropIndex('admin_users_company_id_index');
            $table->dropIndex('admin_users_user_type_index');
            $table->dropIndex('admin_users_card_number_index');
            $table->dropIndex('admin_users_dependent_id_index');
        });
    }
}
