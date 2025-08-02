<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnterprisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enterprises', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('short_name');
            $table->text('details')->nullable();
            $table->string('logo')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamp('expiry')->nullable();
            $table->unsignedBigInteger('administrator_id')->nullable();
            $table->string('subdomain')->nullable()->unique();
            $table->string('color')->nullable();
            $table->text('welcome_message')->nullable();
            $table->string('type')->nullable()->default('Hospital');
            $table->string('phone_number_2')->nullable();
            $table->string('p_o_box')->nullable();
            $table->string('hm_signature')->nullable();
            $table->string('dos_signature')->nullable();
            $table->string('bursar_signature')->nullable();
            $table->string('dp_year')->nullable();
            $table->string('school_pay_code')->nullable();
            $table->string('school_pay_password')->nullable();
            $table->boolean('has_theology')->default(false);
            $table->unsignedBigInteger('dp_term_id')->nullable();
            $table->string('motto')->nullable();
            $table->string('website')->nullable();
            $table->string('hm_name')->nullable();
            $table->decimal('wallet_balance', 10, 2)->default(0);
            $table->boolean('can_send_messages')->default(true);
            $table->boolean('has_valid_lisence')->default(true);
            $table->string('school_pay_status')->nullable();
            $table->string('sec_color')->nullable();
            $table->boolean('school_pay_import_automatically')->default(false);
            $table->date('school_pay_last_accepted_date')->nullable();
            $table->string('status')->default('Active');
            $table->string('timezone')->default('UTC');
            $table->string('currency')->default('USD');
            $table->string('language')->default('en');
            $table->integer('max_users')->default(100);
            $table->integer('storage_limit')->default(1000); // In MB
            
            $table->index(['administrator_id']);
            $table->index(['status']);
            $table->index(['subdomain']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('enterprises');
    }
}
