<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSyncQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sync_queues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('enterprise_id');
            $table->string('device_id', 255);
            $table->string('queue_name', 100); // Name of the queue (e.g., 'consultation_create', 'patient_update')
            $table->json('payload'); // The data to be processed
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(3);
            $table->timestamp('available_at')->nullable(); // When job should be processed
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('dependencies')->nullable(); // Other queue items this depends on
            $table->timestamps();

            // Indexes for queue processing (without foreign keys for now)
            $table->index(['status', 'priority', 'available_at']);
            $table->index(['device_id', 'status']);
            $table->index(['user_id', 'enterprise_id']);
            $table->index('queue_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sync_queues');
    }
}
