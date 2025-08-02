<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnterpriseIdToExistingTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = [
            'users',
            'companies',
            'departments',
            'projects',
            'project_sections',
            'tasks',
            'meetings',
            'events',
            'consultations',
            'medical_services',
            'medical_service_items',
            'billing_items',
            'payment_records',
            'stock_item_categories',
            'stock_items',
            'stock_out_records',
            'patient_records',
            'treatment_records',
            'treatment_record_items',
            'card_records',
            'dose_items',
            'dose_item_records',
            'clients',
            'targets',
            'report_models',
            'admin_users'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $blueprint) use ($table) {
                    if (!Schema::hasColumn($table, 'enterprise_id')) {
                        $blueprint->unsignedBigInteger('enterprise_id')->default(1)->after('id');
                        $blueprint->index(['enterprise_id']);
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = [
            'users',
            'companies',
            'departments',
            'projects',
            'project_sections',
            'tasks',
            'meetings',
            'events',
            'consultations',
            'medical_services',
            'medical_service_items',
            'billing_items',
            'payment_records',
            'stock_item_categories',
            'stock_items',
            'stock_out_records',
            'patient_records',
            'treatment_records',
            'treatment_record_items',
            'card_records',
            'dose_items',
            'dose_item_records',
            'clients',
            'targets',
            'report_models',
            'admin_users'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'enterprise_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropIndex(['enterprise_id']);
                    $blueprint->dropColumn('enterprise_id');
                });
            }
        }
    }
}
