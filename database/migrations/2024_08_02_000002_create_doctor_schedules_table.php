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
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('admin_users')->onDelete('cascade');
            
            // Schedule details
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('slot_duration_minutes')->default(30);
            $table->integer('break_duration_minutes')->default(0);
            
            // Availability settings
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            
            // Capacity management
            $table->integer('max_patients_per_slot')->default(1);
            $table->integer('buffer_time_minutes')->default(5); // Between appointments
            
            // Break times (JSON array of {start_time, end_time, title})
            $table->json('break_times')->nullable();
            
            // Special dates (JSON array of {date, is_available, custom_hours})
            $table->json('special_dates')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['doctor_id', 'day_of_week', 'is_active']);
            $table->index(['enterprise_id', 'effective_from', 'effective_to']);
            
            // Ensure no overlapping schedules for same doctor/day
            $table->unique(['doctor_id', 'day_of_week', 'start_time', 'end_time', 'effective_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_schedules');
    }
};
