<?php

use App\Models\Consultation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoseItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dose_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Consultation::class, 'consultation_id');
            $table->text('medicine')->nullable();
            $table->integer('quantity')->nullable();
            $table->text('units')->nullable();
            $table->integer('times_per_day')->nullable();
            $table->integer('number_of_days')->nullable();
            $table->string('is_processed')->nullable()->default('No');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dose_items');
    }
}
