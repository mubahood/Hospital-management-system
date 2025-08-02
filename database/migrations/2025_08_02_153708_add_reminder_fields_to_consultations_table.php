<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReminderFieldsToConsultationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consultations', function (Blueprint $table) {
            // Only add reminder timestamp fields (avoiding duplicate columns)
            if (!Schema::hasColumn('consultations', 'reminder_24h_sent_at')) {
                $table->timestamp('reminder_24h_sent_at')->nullable()->comment('When 24-hour reminder was sent');
            }
            if (!Schema::hasColumn('consultations', 'reminder_2h_sent_at')) {
                $table->timestamp('reminder_2h_sent_at')->nullable()->comment('When 2-hour reminder was sent');
            }
            if (!Schema::hasColumn('consultations', 'reminder_now_sent_at')) {
                $table->timestamp('reminder_now_sent_at')->nullable()->comment('When immediate reminder was sent');
            }
            
            // Add cancellation reason if not exists
            if (!Schema::hasColumn('consultations', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->comment('Reason for cancellation');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consultations', function (Blueprint $table) {
            // Drop columns that we added
            $columns = ['reminder_24h_sent_at', 'reminder_2h_sent_at', 'reminder_now_sent_at', 'cancellation_reason'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('consultations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
