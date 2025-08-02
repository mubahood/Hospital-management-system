<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSyncRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sync_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('enterprise_id');
            $table->string('device_id', 255);
            $table->string('table_name', 100); // Which table is being synced
            $table->unsignedBigInteger('record_id'); // ID of the record in the table
            $table->enum('operation', ['create', 'update', 'delete']); // Type of operation
            $table->json('data')->nullable(); // The actual data for offline operations
            $table->json('metadata')->nullable(); // Additional sync metadata
            $table->enum('status', ['pending', 'synced', 'failed', 'conflict'])->default('pending');
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_attempted_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('client_timestamp')->nullable(); // When the action happened on the mobile device
            $table->timestamp('server_timestamp')->nullable(); // When it was processed by server
            $table->string('client_version', 20)->nullable(); // App version for compatibility
            $table->string('sync_hash', 64)->nullable(); // Hash for conflict detection
            $table->timestamps();

            // Indexes for performance (without foreign keys for now)
            $table->index(['user_id', 'enterprise_id']);
            $table->index(['device_id', 'status']);
            $table->index(['table_name', 'record_id']);
            $table->index(['status', 'created_at']);
            $table->index('client_timestamp');
            $table->index(['device_id', 'table_name', 'record_id'], 'sync_records_device_table_record_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sync_records');
    }
}
