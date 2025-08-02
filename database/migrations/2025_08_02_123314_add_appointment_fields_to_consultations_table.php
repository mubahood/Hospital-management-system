<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAppointmentFieldsToConsultationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consultations', function (Blueprint $table) {
            // Appointment scheduling fields
            $table->unsignedBigInteger('doctor_id')->nullable()->after('specialist_id');
            $table->datetime('appointment_date')->nullable()->after('preferred_date_and_time');
            $table->time('appointment_start_time')->nullable();
            $table->time('appointment_end_time')->nullable();
            $table->integer('duration_minutes')->default(30);
            
            // Appointment status and management
            $table->enum('appointment_status', ['scheduled', 'confirmed', 'cancelled', 'completed', 'no-show'])->default('scheduled');
            $table->enum('appointment_priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('appointment_type', ['consultation', 'follow_up', 'check_up', 'procedure', 'emergency'])->default('consultation');
            
            // Recurring appointments
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_pattern', ['daily', 'weekly', 'bi_weekly', 'monthly', 'yearly'])->nullable();
            $table->integer('recurrence_interval')->default(1);
            $table->json('recurrence_days')->nullable(); // For weekly: [1,3,5] = Mon,Wed,Fri
            $table->date('recurrence_end_date')->nullable();
            $table->unsignedBigInteger('parent_consultation_id')->nullable();
            
            // Room and location
            $table->unsignedBigInteger('room_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            
            // Confirmation and cancellation tracking
            $table->datetime('confirmed_at')->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->datetime('cancelled_at')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            // Reminders and notifications
            $table->boolean('reminder_sent')->default(false);
            $table->datetime('reminder_sent_at')->nullable();
            $table->json('notification_preferences')->nullable();
            
            // Additional appointment metadata
            $table->text('appointment_notes')->nullable();
            $table->json('preparation_instructions')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            
            // Indexes for better performance
            $table->index(['appointment_date', 'doctor_id']);
            $table->index(['appointment_status']);
            $table->index(['department_id', 'appointment_date']);
            $table->index(['room_id', 'appointment_date']);
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
            // Drop indexes
            $table->dropIndex(['appointment_date', 'doctor_id']);
            $table->dropIndex(['appointment_status']);
            $table->dropIndex(['department_id', 'appointment_date']);
            $table->dropIndex(['room_id', 'appointment_date']);
            
            // Drop columns
            $table->dropColumn([
                'doctor_id',
                'appointment_date',
                'appointment_start_time',
                'appointment_end_time',
                'duration_minutes',
                'appointment_status',
                'appointment_priority',
                'appointment_type',
                'is_recurring',
                'recurrence_pattern',
                'recurrence_interval',
                'recurrence_days',
                'recurrence_end_date',
                'parent_consultation_id',
                'room_id',
                'department_id',
                'confirmed_at',
                'confirmed_by',
                'cancelled_at',
                'cancelled_by',
                'cancellation_reason',
                'reminder_sent',
                'reminder_sent_at',
                'notification_preferences',
                'appointment_notes',
                'preparation_instructions',
                'estimated_cost'
            ]);
        });
    }
}
