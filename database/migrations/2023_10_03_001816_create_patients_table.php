<?php

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Administrator::class);
            $table->text('first_name')->nullable();
            $table->text('last_name')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('phone_number_1')->nullable();
            $table->string('phone_number_2')->nullable();
            $table->string('email')->nullable();
            $table->string('occupation')->nullable();
            $table->text('address')->nullable();
            $table->string('how_you_knew_us')->nullable();
            $table->text('details')->nullable();
        });
    }
    /*				
	
user_type	
sex	
reg_number	
country	
occupation	
profile_photo_large	
phone_number	
location_lat	
location_long	
facebook	
twitter	
whatsapp	
linkedin	
website	
other_link	
cv	
language	
about	
	
created_at	
updated_at	
remember_token	
avatar	
name	
campus_id	
complete_profile	
title	
dob	
intro
*/
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patients');
    }
}
