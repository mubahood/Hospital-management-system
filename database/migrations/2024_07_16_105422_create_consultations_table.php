<?php

use App\Models\Company;
use App\Models\User;
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
        // Schema::drop('consultations');
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignIdFor(User::class, 'patient_id');
            $table->foreignIdFor(User::class, 'receptionist_id');
            $table->foreignIdFor(Company::class, 'company_id');
            $table->string('main_status')->nullable()->default('Pending');
            $table->text('patient_name')->nullable();
            $table->text('patient_contact')->nullable();
            $table->text('contact_address')->nullable();
            $table->text('consultation_number')->nullable();
            $table->text('preferred_date_and_time')->nullable();
            $table->text('services_requested')->nullable();
            $table->text('reason_for_consultation')->nullable();
            $table->text('main_remarks')->nullable();
            $table->string('request_status')->nullable()->default('Pending');
            $table->string('request_date')->nullable();
            $table->text('request_remarks')->nullable();
            $table->text('receptionist_comment')->nullable();
            $table->string('temperature')->nullable();
            $table->string('weight')->nullable();
            $table->string('height')->nullable();
            $table->string('bmi')->nullable();
            $table->integer('total_charges')->nullable();
            $table->integer('total_paid')->nullable();
            $table->integer('total_due')->nullable();
            $table->string('payemnt_status')->nullable()->default('Not Paid');
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
