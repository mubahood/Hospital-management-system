<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('name')->nullable();
            $table->text('short_name')->nullable();
            $table->text('details')->nullable();
            $table->text('logo')->nullable();
            $table->text('phone_number')->nullable();
            $table->text('phone_number_2')->nullable();
            $table->text('p_o_box')->nullable();
            $table->text('email')->nullable();
            $table->text('address')->nullable();
            $table->text('website')->nullable();
            $table->string('subdomain')->nullable();
            $table->string('color')->nullable();
            $table->string('welcome_message')->nullable();
            $table->string('type')->nullable();
            $table->string('wallet_balance')->nullable();
            $table->string('can_send_messages')->nullable();
            $table->string('has_valid_lisence')->nullable();
            $table->integer('administrator_id')->default(1);
            $table->integer('dp_year')->nullable();
            $table->integer('active_year')->nullable();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
