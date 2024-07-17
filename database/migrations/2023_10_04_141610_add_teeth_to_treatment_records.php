<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTeethToTreatmentRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('treatment_records', function (Blueprint $table) {
            $table->string('upper_incisors')->nullable()->default('-');
            $table->string('upper_canines')->nullable()->default('-');
            $table->string('upper_premolars')->nullable()->default('-');
            $table->string('upper_molars')->nullable()->default('-');
            $table->string('lower_incisors')->nullable()->default('-');
            $table->string('lower_canines')->nullable()->default('-');
            $table->string('lower_premolars')->nullable()->default('-');
            $table->string('lower_molars')->nullable()->default('-');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('treatment_records', function (Blueprint $table) {
            //
        });
    }
}
