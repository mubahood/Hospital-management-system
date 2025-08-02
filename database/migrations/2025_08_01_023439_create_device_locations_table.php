<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('enterprise_id');
            $table->string('device_id', 255);
            $table->decimal('latitude', 10, 8); // Latitude with high precision
            $table->decimal('longitude', 11, 8); // Longitude with high precision
            $table->decimal('altitude', 8, 2)->nullable(); // Altitude in meters
            $table->decimal('accuracy', 8, 2)->nullable(); // Accuracy in meters
            $table->decimal('heading', 6, 2)->nullable(); // Compass heading (0-360 degrees)
            $table->decimal('speed', 8, 2)->nullable(); // Speed in m/s
            $table->enum('location_type', ['gps', 'network', 'passive', 'fused'])->default('gps');
            $table->timestamp('location_timestamp'); // When the location was recorded by device
            $table->json('additional_data')->nullable(); // Extra location metadata
            $table->boolean('is_emergency')->default(false); // Emergency location flag
            $table->string('address', 500)->nullable(); // Reverse geocoded address
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['user_id', 'enterprise_id']);
            $table->index(['device_id', 'location_timestamp']);
            $table->index(['latitude', 'longitude']); // For geospatial queries
            $table->index(['is_emergency', 'location_timestamp']);
            $table->index('location_timestamp'); // For time-based queries
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_locations');
    }
}
