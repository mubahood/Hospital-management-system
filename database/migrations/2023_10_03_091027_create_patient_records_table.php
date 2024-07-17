<?php

use App\Models\Patient;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Administrator::class);
            $table->foreignIdFor(Patient::class);
            $table->text('medications_and_allergies')->nullable();
            $table->text('existing_medical_conditions')->nullable();
            $table->text('past_surgeries_or_hospitalizations')->nullable();
            $table->text('history_of_smoking')->nullable();
            $table->text('history_of_alcohol')->nullable();
            $table->text('any_other_relevant_medical_history')->nullable();
            $table->text('chief_complaint')->nullable();
            $table->text('date_of_the_last_dental_visit')->nullable();
            $table->text('previous_dental_treatments')->nullable();
            $table->text('dental_insurance_information')->nullable();
            $table->text('intraoral_and_extraoral_photographs')->nullable();
            $table->text('radiographic_images')->nullable();
            $table->text('periodontal_assessment_gum_health')->nullable();
            $table->text('oral_cancer_screening')->nullable();
            $table->text('tooth_charting_notations')->nullable();
            $table->text('occlusion_bite_assessment')->nullable();
            $table->text('diagnosis_outcome')->nullable();
            $table->text('proposed_dental_treatments')->nullable();
            $table->text('priority_and_urgency_of_treatments')->nullable();
            $table->text('cost_estimates')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_records');
    }
}
