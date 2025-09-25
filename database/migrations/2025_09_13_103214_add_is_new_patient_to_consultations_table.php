<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsNewPatientToConsultationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->boolean('is_new_patient')->default(false)->after('patient_id');
            
            // Add fields for new patient information (will be used temporarily during creation)
            $table->string('new_patient_first_name')->nullable()->after('is_new_patient');
            $table->string('new_patient_last_name')->nullable()->after('new_patient_first_name');
            $table->string('new_patient_email')->nullable()->after('new_patient_last_name');
            $table->string('new_patient_phone')->nullable()->after('new_patient_email');
            $table->string('new_patient_address')->nullable()->after('new_patient_phone');
            $table->date('new_patient_date_of_birth')->nullable()->after('new_patient_address');
            $table->enum('new_patient_gender', ['Male', 'Female', 'Other'])->nullable()->after('new_patient_date_of_birth');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn([
                'is_new_patient',
                'new_patient_first_name',
                'new_patient_last_name', 
                'new_patient_email',
                'new_patient_phone',
                'new_patient_address',
                'new_patient_date_of_birth',
                'new_patient_gender'
            ]);
        });
    }
}
