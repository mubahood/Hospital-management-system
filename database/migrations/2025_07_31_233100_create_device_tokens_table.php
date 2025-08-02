<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('enterprise_id');
            $table->string('device_token', 500);
            $table->string('device_type')->default('mobile'); // mobile, web, tablet
            $table->string('platform')->nullable(); // ios, android, web
            $table->string('app_version')->nullable();
            $table->string('device_model')->nullable();
            $table->string('os_version')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            // Indexes (without foreign keys for now)
            $table->index(['user_id', 'enterprise_id']);
            $table->index(['device_token']);
            $table->index(['is_active']);
            
            // Unique constraint to prevent duplicate tokens per user
            $table->unique(['user_id', 'device_token']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_tokens');
    }
}
