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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enterprise_id');
            // $table->foreignId('enterprise_id')->constrained('enterprises')->onDelete('cascade');
            
            // Core appointment details
            $table->string('appointment_number')->unique();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('department_id')->nullable();
            // $table->foreignId('patient_id')->constrained('admin_users')->onDelete('cascade');
            // $table->foreignId('doctor_id')->constrained('admin_users')->onDelete('cascade');
            // $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            
            // Scheduling details
            $table->datetime('appointment_date');
            $table->datetime('appointment_end_date');
            $table->integer('duration_minutes')->default(30);
            
            // Appointment type and priority
            $table->enum('appointment_type', [
                'consultation', 'follow_up', 'surgery', 'procedure', 
                'lab_test', 'imaging', 'therapy', 'vaccination', 'emergency'
            ])->default('consultation');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            // Status management
            $table->enum('status', [
                'scheduled', 'confirmed', 'in_progress', 'completed', 
                'cancelled', 'no_show', 'rescheduled'
            ])->default('scheduled');
            
            // Appointment details
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->text('preparation_instructions')->nullable();
            $table->json('services_requested')->nullable();
            
            // Resource booking
            $table->unsignedBigInteger('room_id')->nullable();
            $table->json('equipment_ids')->nullable(); // Array of equipment IDs
            // $table->foreignId('room_id')->nullable()->constrained()->onDelete('set null');
            
            // Recurring appointments
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_type', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();
            $table->integer('recurrence_interval')->default(1);
            $table->date('recurrence_end_date')->nullable();
            $table->unsignedBigInteger('parent_appointment_id')->nullable();
            // $table->foreignId('parent_appointment_id')->nullable()->constrained('appointments')->onDelete('cascade');
            
            // Communication
            $table->boolean('sms_reminder_sent')->default(false);
            $table->boolean('email_reminder_sent')->default(false);
            $table->datetime('reminder_sent_at')->nullable();
            $table->boolean('confirmation_required')->default(true);
            $table->datetime('confirmed_at')->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();
            // $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Tracking
            $table->datetime('checked_in_at')->nullable();
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            // $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            // $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Cancellation details
            $table->text('cancellation_reason')->nullable();
            $table->datetime('cancelled_at')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            // $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['enterprise_id', 'appointment_date']);
            $table->index(['doctor_id', 'appointment_date']);
            $table->index(['patient_id', 'appointment_date']);
            $table->index(['status', 'appointment_date']);
            $table->index(['appointment_type', 'appointment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
