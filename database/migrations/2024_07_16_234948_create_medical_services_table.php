<?php

use App\Models\Consultation;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedicalServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medical_services', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Consultation::class, 'consultation_id');
            $table->foreignIdFor(User::class, 'receptionist_id')->nullable();
            $table->foreignIdFor(User::class, 'patient_id')->nullable();
            $table->foreignIdFor(User::class, 'assigned_to_id')->nullable();
            $table->string('type')->nullable()->default('Pending');
            $table->string('status')->nullable()->default('Pending');
            $table->string('remarks')->nullable();
            $table->text('instruction')->nullable();
            $table->text('specialist_outcome')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medical_services');
    }
}
