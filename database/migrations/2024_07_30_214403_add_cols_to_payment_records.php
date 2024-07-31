<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToPaymentRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_records', function (Blueprint $table) {
            $table->foreignIdFor(User::class, 'cash_received_by_id')->nullable();
            $table->foreignIdFor(User::class, 'created_by_id')->nullable();
            $table->text('cash_receipt_number')->nullable();
            $table->foreignIdFor(User::class, 'card_id')->nullable();
            $table->foreignIdFor(Company::class, 'company_id')->nullable();
            $table->text('card_number')->nullable();
            $table->text('card_type')->nullable();

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
        Schema::table('payment_records', function (Blueprint $table) {
            //
        });
    }
}
