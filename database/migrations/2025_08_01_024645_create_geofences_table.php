<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeofencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('geofences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enterprise_id');
            $table->string('name', 200); // Hospital, clinic, emergency zone name
            $table->text('description')->nullable();
            $table->decimal('center_latitude', 10, 8); // Center point latitude
            $table->decimal('center_longitude', 11, 8); // Center point longitude
            $table->decimal('radius', 10, 2); // Radius in meters
            $table->enum('fence_type', ['hospital', 'clinic', 'emergency_zone', 'restricted_area', 'parking'])->default('hospital');
            $table->enum('trigger_action', ['check_in', 'check_out', 'both', 'alert'])->default('both');
            $table->boolean('is_active')->default(true);
            $table->json('trigger_settings')->nullable(); // Custom trigger configurations
            $table->string('address', 500)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('contact_email', 100)->nullable();
            $table->json('operating_hours')->nullable(); // Store operating hours
            $table->json('services_available')->nullable(); // Available medical services
            $table->timestamps();

            // Indexes for location-based queries
            $table->index(['enterprise_id', 'is_active']);
            $table->index(['center_latitude', 'center_longitude']); // For geospatial queries
            $table->index('fence_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('geofences');
    }
}
