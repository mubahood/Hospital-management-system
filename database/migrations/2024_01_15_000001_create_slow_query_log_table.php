<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for slow query logging table
 * 
 * Creates table to track and analyze slow database queries
 */
class CreateSlowQueryLogTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('slow_query_log', function (Blueprint $table) {
            $table->id();
            $table->string('sql_hash', 32)->index(); // MD5 hash of SQL for grouping
            $table->longText('sql'); // The actual SQL query
            $table->json('bindings')->nullable(); // Query bindings
            $table->decimal('execution_time', 10, 2); // Execution time in milliseconds
            $table->unsignedBigInteger('enterprise_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('connection_name', 50)->nullable();
            $table->string('request_url', 500)->nullable();
            $table->string('request_method', 10)->nullable();
            $table->json('request_headers')->nullable();
            $table->timestamps();

            // Indexes for performance analysis
            $table->index(['enterprise_id', 'execution_time']);
            $table->index(['sql_hash', 'created_at']);
            $table->index(['execution_time', 'created_at']);
            $table->index(['user_id', 'created_at']);

            // Foreign key constraints - commented out as users table may not exist
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slow_query_log');
    }
}
