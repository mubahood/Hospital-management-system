<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DeprecatePatientAndAppointmentTables extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration marks the patients and appointments tables as deprecated
     * since their functionality has been consolidated into users and consultations tables.
     *
     * @return void
     */
    public function up()
    {
        // Add deprecation comment to patients table
        if (Schema::hasTable('patients')) {
            DB::statement("ALTER TABLE patients COMMENT = 'DEPRECATED: Patient data moved to users table. Use users with user_type=patient instead.'");
        }
        
        // Add deprecation comment to appointments table  
        if (Schema::hasTable('appointments')) {
            DB::statement("ALTER TABLE appointments COMMENT = 'DEPRECATED: Appointment data moved to consultations table. Use consultations with appointment fields instead.'");
        }
        
        // Optional: You can uncomment these lines to actually drop the tables
        // if you're confident all data has been migrated successfully
        
        // Schema::dropIfExists('patients');
        // Schema::dropIfExists('appointments');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove deprecation comments
        if (Schema::hasTable('patients')) {
            DB::statement("ALTER TABLE patients COMMENT = ''");
        }
        
        if (Schema::hasTable('appointments')) {
            DB::statement("ALTER TABLE appointments COMMENT = ''");
        }
    }
}
