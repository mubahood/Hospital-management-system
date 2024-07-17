<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeBelongsToCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->string('belongs_to_company', 100)->nullable(); // Adjust length as needed
            $table->enum('card_status', ['Pending', 'Active', 'Deactive'])->default('Pending');
            $table->string('card_number', 20)->nullable(); // Adjust length as needed
            $table->decimal('card_balance', 10, 2)->nullable();
            $table->boolean('card_accepts_credit')->default(false);
            $table->decimal('card_max_credit', 10, 2)->nullable();
            $table->boolean('card_accepts_cash')->default(false);
            $table->boolean('is_dependent')->default(false);
            $table->enum('dependent_status', ['Pending', 'Active', 'Deactive'])->default('Pending');
            $table->integer('dependent_id')->nullable();
            $table->date('card_expiry')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            //
        });
    }
}
