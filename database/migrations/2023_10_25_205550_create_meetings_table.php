<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Company::class)->nullable()->default(1)->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'created_by')->nullable()->default(1);
            $table->text('name')->nullable();
            $table->text('details')->nullable();
            $table->text('minutes_of_meeting')->nullable();
            $table->text('location')->nullable();
            $table->text('location_gps_latitude')->nullable();
            $table->text('location_gps_longitude')->nullable();
            $table->text('meeting_start_time')->nullable();
            $table->text('meeting_end_time')->nullable();
            $table->text('attendance_list_pictures')->nullable();
            $table->text('members_pictures')->nullable();
            $table->text('attachments')->nullable();
            $table->text('members_present')->nullable();
            $table->text('other_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meetings');
    }
}
