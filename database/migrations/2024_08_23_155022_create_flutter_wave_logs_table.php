<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFlutterWaveLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flutter_wave_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('consultation_id');
            $table->string('status')->nullable()->default('Pending');
            $table->text('flutterwave_reference')->nullable();
            $table->text('flutterwave_payment_type')->nullable();
            $table->text('flutterwave_payment_status')->nullable();
            $table->text('flutterwave_payment_message')->nullable();
            $table->text('flutterwave_payment_code')->nullable();
            $table->text('flutterwave_payment_data')->nullable();
            $table->text('flutterwave_payment_link')->nullable();
            $table->text('flutterwave_payment_amount')->nullable();
            $table->text('flutterwave_payment_customer_name')->nullable();
            $table->text('flutterwave_payment_customer_id')->nullable();
            $table->text('flutterwave_payment_customer_email')->nullable();
            $table->text('flutterwave_payment_customer_phone_number')->nullable();
            $table->text('flutterwave_payment_customer_full_name')->nullable();
            $table->text('flutterwave_payment_customer_created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('flutter_wave_logs');
    }
}