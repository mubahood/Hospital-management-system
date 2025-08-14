<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ConsolidatePatientAppointmentModels extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration consolidates:
     * 1. Patient data into Users table (patients become users who can login)
     * 2. Appointment data into Consultations table (complete patient journey)
     *
     * @return void
     */
    public function up()
    {
        // Step 1: Migrate any existing patient data to users table
        if (Schema::hasTable('patients')) {
            $this->migratePatientDataToUsers();
        }

        // Step 2: Migrate any existing appointment data to consultations table
        if (Schema::hasTable('appointments')) {
            $this->migrateAppointmentDataToConsultations();
        }

        // Step 3: Update foreign key references
        $this->updateForeignKeyReferences();

        // Step 4: Add any missing fields to users table for complete patient management
        $this->enhanceUsersTableForPatients();

        // Step 5: Add any missing fields to consultations table for complete appointment management
        $this->enhanceConsultationsTableForAppointments();
    }

    /**
     * Migrate existing patient data to users table
     */
    private function migratePatientDataToUsers()
    {
        // Get all patients and convert them to users
        $patients = DB::table('patients')->get();
        
        foreach ($patients as $patient) {
            // Check if user already exists with this patient_id
            $existingUser = DB::table('users')->where('id', $patient->id)->first();
            
            if (!$existingUser) {
                // Create new user from patient data
                $userData = [
                    'id' => $patient->id,
                    'name' => $patient->name ?? 'Patient',
                    'email' => $patient->email ?? "patient{$patient->id}@hospital.local",
                    'password' => bcrypt('password123'), // Default password
                    'user_type' => 'patient',
                    'created_at' => $patient->created_at ?? now(),
                    'updated_at' => $patient->updated_at ?? now(),
                    'enterprise_id' => $patient->enterprise_id ?? null,
                ];

                // Add any additional patient fields that exist
                $patientColumns = Schema::getColumnListing('patients');
                foreach ($patientColumns as $column) {
                    if (!in_array($column, ['id', 'created_at', 'updated_at']) && 
                        Schema::hasColumn('users', $column) && 
                        isset($patient->$column)) {
                        $userData[$column] = $patient->$column;
                    }
                }

                DB::table('users')->insert($userData);
            }
        }
    }

    /**
     * Migrate existing appointment data to consultations table
     */
    private function migrateAppointmentDataToConsultations()
    {
        // Get all appointments and convert them to consultations
        $appointments = DB::table('appointments')->get();
        
        foreach ($appointments as $appointment) {
            // Create consultation from appointment data
            $consultationData = [
                'enterprise_id' => $appointment->enterprise_id,
                'consultation_number' => $appointment->appointment_number,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'department_id' => $appointment->department_id,
                'appointment_date' => $appointment->appointment_date,
                'appointment_end_date' => $appointment->appointment_end_date,
                'duration_minutes' => $appointment->duration_minutes,
                'appointment_type' => $appointment->appointment_type,
                'priority' => $appointment->priority,
                'status' => $appointment->status,
                'main_status' => $appointment->status,
                'reason_for_consultation' => $appointment->reason,
                'main_remarks' => $appointment->notes,
                'preparation_instructions' => $appointment->preparation_instructions,
                'room_id' => $appointment->room_id,
                'equipment_ids' => $appointment->equipment_ids,
                'is_recurring' => $appointment->is_recurring,
                'recurrence_type' => $appointment->recurrence_type,
                'recurrence_interval' => $appointment->recurrence_interval,
                'recurrence_end_date' => $appointment->recurrence_end_date,
                'parent_consultation_id' => $appointment->parent_appointment_id,
                'sms_reminder_sent' => $appointment->sms_reminder_sent,
                'email_reminder_sent' => $appointment->email_reminder_sent,
                'reminder_sent_at' => $appointment->reminder_sent_at,
                'confirmation_required' => $appointment->confirmation_required,
                'confirmed_at' => $appointment->confirmed_at,
                'confirmed_by' => $appointment->confirmed_by,
                'checked_in_at' => $appointment->checked_in_at,
                'started_at' => $appointment->started_at,
                'completed_at' => $appointment->completed_at,
                'created_by' => $appointment->created_by,
                'updated_by' => $appointment->updated_by,
                'cancellation_reason' => $appointment->cancellation_reason,
                'cancelled_at' => $appointment->cancelled_at,
                'cancelled_by' => $appointment->cancelled_by,
                'created_at' => $appointment->created_at,
                'updated_at' => $appointment->updated_at,
            ];

            DB::table('consultations')->insert($consultationData);
        }
    }

    /**
     * Update foreign key references throughout the system
     */
    private function updateForeignKeyReferences()
    {
        // Update any references to patient_id to point to users table
        // Update any references to appointment_id to point to consultations table
        
        // Example updates (add more as needed based on your schema):
        
        // Update consultation patient references if they exist
        DB::statement('UPDATE consultations SET patient_id = patient_id WHERE patient_id IS NOT NULL');
        
        // Add more table updates here as needed for your specific schema
    }

    /**
     * Add any missing patient-specific fields to users table
     */
    private function enhanceUsersTableForPatients()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add any additional patient-specific fields that might be missing
            if (!Schema::hasColumn('users', 'blood_type')) {
                $table->string('blood_type')->nullable()->after('medical_history');
            }
            
            if (!Schema::hasColumn('users', 'height')) {
                $table->decimal('height', 8, 2)->nullable()->after('blood_type');
            }
            
            if (!Schema::hasColumn('users', 'weight')) {
                $table->decimal('weight', 8, 2)->nullable()->after('height');
            }
            
            if (!Schema::hasColumn('users', 'emergency_contact_relationship')) {
                $table->string('emergency_contact_relationship')->nullable()->after('emergency_person_phone');
            }
            
            if (!Schema::hasColumn('users', 'preferred_language')) {
                $table->string('preferred_language')->nullable()->after('languages');
            }
            
            if (!Schema::hasColumn('users', 'patient_status')) {
                $table->enum('patient_status', ['active', 'inactive', 'discharged', 'deceased'])->default('active')->after('user_type');
            }
        });
    }

    /**
     * Add any missing appointment-specific fields to consultations table
     */
    private function enhanceConsultationsTableForAppointments()
    {
        Schema::table('consultations', function (Blueprint $table) {
            // Add any additional appointment-specific fields that might be missing
            if (!Schema::hasColumn('consultations', 'appointment_number')) {
                $table->string('appointment_number')->unique()->nullable()->after('consultation_number');
            }
            
            if (!Schema::hasColumn('consultations', 'services_requested_json')) {
                $table->json('services_requested_json')->nullable()->after('services_requested');
            }
            
            if (!Schema::hasColumn('consultations', 'follow_up_required')) {
                $table->boolean('follow_up_required')->default(false)->after('dosage_is_completed');
            }
            
            if (!Schema::hasColumn('consultations', 'follow_up_date')) {
                $table->timestamp('follow_up_date')->nullable()->after('follow_up_required');
            }
            
            if (!Schema::hasColumn('consultations', 'patient_notes')) {
                $table->text('patient_notes')->nullable()->after('main_remarks');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Note: This is a destructive operation and should be used carefully
        
        // Remove added fields from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'blood_type', 'height', 'weight', 'emergency_contact_relationship', 
                'preferred_language', 'patient_status'
            ]);
        });
        
        // Remove added fields from consultations table
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn([
                'appointment_number', 'services_requested_json', 'follow_up_required',
                'follow_up_date', 'patient_notes'
            ]);
        });
        
        // Recreate patients and appointments tables would require separate migrations
        // This rollback is intentionally limited to prevent data loss
    }
}
