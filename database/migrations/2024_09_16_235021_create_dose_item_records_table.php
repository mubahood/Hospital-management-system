<?php

use App\Models\Consultation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoseItemRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dose_item_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Consultation::class, 'consultation_id');
            $table->text('medicine')->nullable();
            $table->integer('quantity')->nullable();
            $table->text('units')->nullable();
            $table->integer('times_per_day')->nullable();
            $table->integer('number_of_days')->nullable();
            $table->string('status')->nullable()->default('Not taken');
            $table->text('remarks')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->dateTime('date_submitted')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dose_item_records');
    }
}
