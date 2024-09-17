<?php

use App\Models\DoseItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreInfoDoseItemRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dose_item_records', function (Blueprint $table) {
            $table->foreignIdFor(DoseItem::class, 'dose_item_id')->nullable();
            $table->string('time_name')->nullable();
            $table->string('time_value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dose_item_records', function (Blueprint $table) {
            //
        });
    }
}
