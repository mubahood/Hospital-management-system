<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsultationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enterprise_id')->nullable();
            $table->string('consultation_number')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->string('patient_name')->nullable();
            $table->string('patient_contact')->nullable();
            $table->text('contact_address')->nullable();
            $table->unsignedBigInteger('receptionist_id')->nullable();
            $table->decimal('consultation_fee', 15, 2)->default(0);
            $table->timestamp('request_date')->nullable();
            $table->text('request_remarks')->nullable();
            $table->text('receptionist_comment')->nullable();
            $table->string('status')->default('pending');
            $table->string('main_status')->default('pending');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->timestamp('preferred_date_and_time')->nullable();
            $table->text('services_requested')->nullable();
            $table->text('reason_for_consultation')->nullable();
            $table->text('main_remarks')->nullable();
            $table->string('request_status')->default('pending');
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('bmi', 8, 2)->nullable();
            $table->decimal('total_charges', 15, 2)->default(0);
            $table->decimal('total_paid', 15, 2)->default(0);
            $table->decimal('total_due', 15, 2)->default(0);
            $table->string('payemnt_status')->default('unpaid');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('fees_total', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->boolean('invoice_processed')->default(false);
            $table->text('invoice_pdf')->nullable();
            $table->timestamp('invoice_process_date')->nullable();
            $table->string('bill_status')->default('unpaid');
            $table->string('specify_specialist')->nullable();
            $table->unsignedBigInteger('specialist_id')->nullable();
            $table->text('report_link')->nullable();
            $table->text('dosage_progress')->nullable();
            $table->boolean('dosage_is_completed')->default(false);
            // Enhanced appointment scheduling fields
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->timestamp('appointment_date')->nullable();
            $table->timestamp('appointment_end_date')->nullable();
            $table->integer('duration_minutes')->default(30);
            $table->string('appointment_type')->default('consultation');
            $table->string('priority')->default('normal');
            $table->unsignedBigInteger('room_id')->nullable();
            $table->text('equipment_ids')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_type')->nullable();
            $table->integer('recurrence_interval')->nullable();
            $table->date('recurrence_end_date')->nullable();
            $table->unsignedBigInteger('parent_consultation_id')->nullable();
            $table->text('preparation_instructions')->nullable();
            $table->boolean('sms_reminder_sent')->default(false);
            $table->boolean('email_reminder_sent')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();
            $table->boolean('confirmation_required')->default(false);
            $table->timestamp('confirmed_at')->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index('enterprise_id');
            $table->index('patient_id');
            $table->index('doctor_id');
            $table->index('department_id');
            $table->index('appointment_date');
            $table->index('status');
            $table->index('main_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consultations');
    }
}
