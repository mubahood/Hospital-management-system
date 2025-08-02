<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for audit logs table
 * 
 * Creates comprehensive audit logging table for security and compliance
 */
class CreateAuditLogsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('audit_id', 100)->unique()->index();
            $table->string('event_type', 50)->index(); // request_start, request_complete, data_change, etc.
            
            // User and Enterprise Information
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('enterprise_id')->nullable()->index();
            
            // Request Information
            $table->string('ip_address', 45)->nullable()->index(); // IPv6 support
            $table->text('user_agent')->nullable();
            $table->string('method', 10)->nullable(); // GET, POST, PUT, DELETE, etc.
            $table->text('url')->nullable();
            $table->string('route', 255)->nullable()->index();
            $table->string('session_id', 255)->nullable()->index();
            
            // Response Information
            $table->integer('status_code')->nullable()->index();
            $table->decimal('duration_ms', 10, 2)->nullable(); // Request duration in milliseconds
            $table->bigInteger('memory_usage')->nullable(); // Memory usage in bytes
            $table->integer('database_queries')->nullable(); // Number of database queries
            $table->bigInteger('response_size')->nullable(); // Response size in bytes
            
            // Data Information
            $table->longText('parameters')->nullable(); // JSON of request parameters (sanitized)
            $table->longText('data_changes')->nullable(); // JSON of data changes (before/after)
            $table->longText('error_details')->nullable(); // JSON of error information
            $table->integer('affected_records')->nullable(); // Number of records affected
            
            // Additional metadata
            $table->text('tags')->nullable(); // Comma-separated tags for categorization
            $table->text('notes')->nullable(); // Additional notes
            
            $table->timestamps();
            
            // Foreign key constraints - commented out as users table may not exist
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for common queries
            $table->index(['enterprise_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['event_type', 'created_at']);
            $table->index(['status_code', 'created_at']);
            $table->index(['ip_address', 'created_at']);
            
            // Composite indexes for complex queries
            $table->index(['enterprise_id', 'user_id', 'event_type']);
            $table->index(['enterprise_id', 'event_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
}
