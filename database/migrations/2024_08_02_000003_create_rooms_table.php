<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('companies')->onDelete('cascade');
            
            // Room details
            $table->string('room_number')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('room_type', [
                'consultation', 'surgery', 'procedure', 'laboratory', 
                'imaging', 'therapy', 'emergency', 'ward', 'icu', 'pharmacy'
            ]);
            
            // Capacity and features
            $table->integer('capacity')->default(1);
            $table->json('equipment')->nullable(); // Available equipment
            $table->json('features')->nullable(); // Room features/amenities
            
            // Location and contact
            $table->string('floor')->nullable();
            $table->string('building')->nullable();
            $table->string('extension')->nullable();
            
            // Status and availability
            $table->boolean('is_active')->default(true);
            $table->boolean('is_available')->default(true);
            $table->text('unavailable_reason')->nullable();
            $table->datetime('unavailable_until')->nullable();
            
            // Maintenance and cleaning
            $table->datetime('last_cleaned_at')->nullable();
            $table->datetime('last_maintenance_at')->nullable();
            $table->datetime('next_maintenance_due')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['enterprise_id', 'room_type', 'is_active']);
            $table->index(['room_number', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
