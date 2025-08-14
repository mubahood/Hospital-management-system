<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

class DataValidationAndIntegrity extends Command
{
    protected $signature = 'hospital:validate-data {--fix : Attempt to fix data integrity issues}';
    protected $description = 'Validate data integrity and consistency across hospital database';

    public function handle()
    {
        $this->info('🔍 HOSPITAL DATA VALIDATION & INTEGRITY CHECK');
        $this->info('===============================================');

        $issues = [];

        // 1. Check for orphaned records
        $issues = array_merge($issues, $this->checkOrphanedRecords());

        // 2. Validate required relationships
        $issues = array_merge($issues, $this->validateRelationships());

        // 3. Check data consistency
        $issues = array_merge($issues, $this->checkDataConsistency());

        // 4. Validate business rules
        $issues = array_merge($issues, $this->validateBusinessRules());

        // 5. Check for duplicate records
        $issues = array_merge($issues, $this->checkDuplicateRecords());

        // Summary and recommendations
        $this->displaySummary($issues);

        // Auto-fix option
        if ($this->option('fix') && !empty($issues)) {
            $this->attemptAutoFix($issues);
        }

        return 0;
    }

    private function checkOrphanedRecords()
    {
        $this->info('\n📋 1. CHECKING ORPHANED RECORDS');
        $this->info('===============================');
        
        $issues = [];

        // Get list of existing tables
        $existingTables = [];
        try {
            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                $existingTables[] = $tableName;
            }
        } catch (Exception $e) {
            $this->error("❌ Could not retrieve table list: " . $e->getMessage());
            return [];
        }

        // Check consultations without patients (only if consultations table exists)
        if (in_array('consultations', $existingTables)) {
            try {
                $orphanedConsultations = DB::table('consultations')
                    ->leftJoin('admin_users', 'consultations.patient_id', '=', 'admin_users.id')
                    ->whereNull('admin_users.id')
                    ->count();

                if ($orphanedConsultations > 0) {
                    $issues[] = [
                        'type' => 'orphaned_consultations',
                        'count' => $orphanedConsultations,
                        'severity' => 'high',
                        'description' => "Found {$orphanedConsultations} consultations without valid patients"
                    ];
                    $this->error("❌ Found {$orphanedConsultations} orphaned consultations");
                } else {
                    $this->info('✅ No orphaned consultations found');
                }
            } catch (Exception $e) {
                $this->warn("⚠️ Could not check consultations: " . $e->getMessage());
            }
        } else {
            $this->info('ℹ️ Consultations table not found - skipping check');
        }

        // Check treatment records without patients (only if treatment_records table exists)
        if (in_array('treatment_records', $existingTables)) {
            try {
                $orphanedTreatments = DB::table('treatment_records')
                    ->leftJoin('admin_users', 'treatment_records.patient_id', '=', 'admin_users.id')
                    ->whereNull('admin_users.id')
                    ->count();

                if ($orphanedTreatments > 0) {
                    $issues[] = [
                        'type' => 'orphaned_treatments',
                        'count' => $orphanedTreatments,
                        'severity' => 'high',
                        'description' => "Found {$orphanedTreatments} treatment records without valid patients"
                    ];
                    $this->error("❌ Found {$orphanedTreatments} orphaned treatment records");
                } else {
                    $this->info('✅ No orphaned treatment records found');
                }
            } catch (Exception $e) {
                $this->warn("⚠️ Could not check treatment records: " . $e->getMessage());
            }
        } else {
            $this->info('ℹ️ Treatment records table not found - skipping check');
        }

        // Check appointments without patients or doctors (only if appointments table exists)
        if (in_array('appointments', $existingTables)) {
            try {
                $orphanedAppointments = DB::table('appointments')
                    ->leftJoin('admin_users as patients', 'appointments.patient_id', '=', 'patients.id')
                    ->leftJoin('admin_users as doctors', 'appointments.doctor_id', '=', 'doctors.id')
                    ->where(function($query) {
                        $query->whereNull('patients.id')->orWhereNull('doctors.id');
                    })
                    ->count();

                if ($orphanedAppointments > 0) {
                    $issues[] = [
                        'type' => 'orphaned_appointments',
                        'count' => $orphanedAppointments,
                        'severity' => 'medium',
                        'description' => "Found {$orphanedAppointments} appointments with invalid patient or doctor references"
                    ];
                    $this->error("❌ Found {$orphanedAppointments} orphaned appointments");
                } else {
                    $this->info('✅ No orphaned appointments found');
                }
            } catch (Exception $e) {
                $this->warn("⚠️ Could not check appointments: " . $e->getMessage());
            }
        } else {
            $this->info('ℹ️ Appointments table not found - skipping check');
        }

        return $issues;
    }

    private function validateRelationships()
    {
        $this->info('\n🔗 2. VALIDATING RELATIONSHIPS');
        $this->info('===============================');
        
        $issues = [];

        // Get list of existing tables
        $existingTables = [];
        try {
            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                $existingTables[] = $tableName;
            }
        } catch (Exception $e) {
            $this->error("❌ Could not retrieve table list: " . $e->getMessage());
            return [];
        }

        // Check enterprise relationships
        $usersWithoutEnterprise = DB::table('admin_users')
            ->whereNull('enterprise_id')
            ->count();

        if ($usersWithoutEnterprise > 0) {
            $issues[] = [
                'type' => 'missing_enterprise',
                'count' => $usersWithoutEnterprise,
                'severity' => 'medium',
                'description' => "Found {$usersWithoutEnterprise} users without enterprise assignment"
            ];
            $this->warn("⚠️ Found {$usersWithoutEnterprise} users without enterprise assignment");
        } else {
            $this->info('✅ All users have enterprise assignments');
        }

        // Check stock items without categories (using correct column name)
        if (in_array('stock_items', $existingTables)) {
            try {
                $stockWithoutCategory = DB::table('stock_items')
                    ->whereNull('stock_item_category_id')
                    ->count();

                if ($stockWithoutCategory > 0) {
                    $issues[] = [
                        'type' => 'stock_without_category',
                        'count' => $stockWithoutCategory,
                        'severity' => 'low',
                        'description' => "Found {$stockWithoutCategory} stock items without category"
                    ];
                    $this->warn("⚠️ Found {$stockWithoutCategory} stock items without category");
                } else {
                    $this->info('✅ All stock items have categories');
                }
            } catch (Exception $e) {
                $this->warn("⚠️ Could not check stock items: " . $e->getMessage());
            }
        } else {
            $this->info('ℹ️ Stock items table not found - skipping check');
        }

        return $issues;
    }

    private function checkDataConsistency()
    {
        $this->info('\n🔄 3. CHECKING DATA CONSISTENCY');
        $this->info('===============================');
        
        $issues = [];

        // Check negative stock quantities
        $negativeStock = DB::table('stock_items')
            ->where('current_quantity', '<', 0)
            ->count();

        if ($negativeStock > 0) {
            $issues[] = [
                'type' => 'negative_stock',
                'count' => $negativeStock,
                'severity' => 'high',
                'description' => "Found {$negativeStock} items with negative stock quantities"
            ];
            $this->error("❌ Found {$negativeStock} items with negative stock");
        } else {
            $this->info('✅ No negative stock quantities found');
        }

        // Check appointments in the past without status update
        $pastAppointmentsUnresolved = DB::table('appointments')
            ->where('appointment_date', '<', Carbon::now())
            ->where('status', 'scheduled')
            ->count();

        if ($pastAppointmentsUnresolved > 0) {
            $issues[] = [
                'type' => 'unresolved_past_appointments',
                'count' => $pastAppointmentsUnresolved,
                'severity' => 'medium',
                'description' => "Found {$pastAppointmentsUnresolved} past appointments still marked as scheduled"
            ];
            $this->warn("⚠️ Found {$pastAppointmentsUnresolved} unresolved past appointments");
        } else {
            $this->info('✅ All past appointments have been resolved');
        }

        // Check for future birth dates
        $futureBirthDates = DB::table('admin_users')
            ->where('date_of_birth', '>', Carbon::now())
            ->count();

        if ($futureBirthDates > 0) {
            $issues[] = [
                'type' => 'future_birth_dates',
                'count' => $futureBirthDates,
                'severity' => 'high',
                'description' => "Found {$futureBirthDates} users with future birth dates"
            ];
            $this->error("❌ Found {$futureBirthDates} users with future birth dates");
        } else {
            $this->info('✅ No future birth dates found');
        }

        return $issues;
    }

    private function validateBusinessRules()
    {
        $this->info('\n📊 4. VALIDATING BUSINESS RULES');
        $this->info('===============================');
        
        $issues = [];

        // Check for expired medications still in active stock
        $expiredMedications = DB::table('stock_items')
            ->where('expiry_date', '<', Carbon::now())
            ->where('current_quantity', '>', 0)
            ->count();

        if ($expiredMedications > 0) {
            $issues[] = [
                'type' => 'expired_medications_in_stock',
                'count' => $expiredMedications,
                'severity' => 'high',
                'description' => "Found {$expiredMedications} expired medications still in active stock"
            ];
            $this->error("❌ Found {$expiredMedications} expired medications in stock");
        } else {
            $this->info('✅ No expired medications in active stock');
        }

        // Check for consultations without diagnosis after completion
        $consultationsWithoutDiagnosis = DB::table('consultations')
            ->where('status', 'completed')
            ->where(function($query) {
                $query->whereNull('diagnosis')->orWhere('diagnosis', '');
            })
            ->count();

        if ($consultationsWithoutDiagnosis > 0) {
            $issues[] = [
                'type' => 'completed_consultations_without_diagnosis',
                'count' => $consultationsWithoutDiagnosis,
                'severity' => 'medium',
                'description' => "Found {$consultationsWithoutDiagnosis} completed consultations without diagnosis"
            ];
            $this->warn("⚠️ Found {$consultationsWithoutDiagnosis} completed consultations without diagnosis");
        } else {
            $this->info('✅ All completed consultations have diagnosis');
        }

        // Check for billing items without amounts
        $billingWithoutAmount = DB::table('billing_items')
            ->where(function($query) {
                $query->whereNull('amount')->orWhere('amount', 0);
            })
            ->count();

        if ($billingWithoutAmount > 0) {
            $issues[] = [
                'type' => 'billing_without_amount',
                'count' => $billingWithoutAmount,
                'severity' => 'medium',
                'description' => "Found {$billingWithoutAmount} billing items without proper amounts"
            ];
            $this->warn("⚠️ Found {$billingWithoutAmount} billing items without amounts");
        } else {
            $this->info('✅ All billing items have proper amounts');
        }

        return $issues;
    }

    private function checkDuplicateRecords()
    {
        $this->info('\n🔍 5. CHECKING DUPLICATE RECORDS');
        $this->info('=================================');
        
        $issues = [];

        // Check for duplicate patient records (same phone and name)
        $duplicatePatients = DB::table('admin_users')
            ->selectRaw('phone, name, COUNT(*) as count')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->groupBy('phone', 'name')
            ->having('count', '>', 1)
            ->count();

        if ($duplicatePatients > 0) {
            $issues[] = [
                'type' => 'duplicate_patients',
                'count' => $duplicatePatients,
                'severity' => 'medium',
                'description' => "Found {$duplicatePatients} potential duplicate patient records"
            ];
            $this->warn("⚠️ Found {$duplicatePatients} potential duplicate patients");
        } else {
            $this->info('✅ No duplicate patient records found');
        }

        // Check for duplicate stock items
        $duplicateStockItems = DB::table('stock_items')
            ->selectRaw('name, category_id, COUNT(*) as count')
            ->groupBy('name', 'category_id')
            ->having('count', '>', 1)
            ->count();

        if ($duplicateStockItems > 0) {
            $issues[] = [
                'type' => 'duplicate_stock_items',
                'count' => $duplicateStockItems,
                'severity' => 'low',
                'description' => "Found {$duplicateStockItems} potential duplicate stock items"
            ];
            $this->warn("⚠️ Found {$duplicateStockItems} potential duplicate stock items");
        } else {
            $this->info('✅ No duplicate stock items found');
        }

        return $issues;
    }

    private function displaySummary($issues)
    {
        $this->info('\n📊 VALIDATION SUMMARY');
        $this->info('=====================');

        if (empty($issues)) {
            $this->info('🎉 Excellent! No data integrity issues found.');
            return;
        }

        $highSeverity = collect($issues)->where('severity', 'high')->count();
        $mediumSeverity = collect($issues)->where('severity', 'medium')->count();
        $lowSeverity = collect($issues)->where('severity', 'low')->count();

        $this->info("Total Issues Found: " . count($issues));
        if ($highSeverity > 0) $this->error("🔴 High Priority: {$highSeverity}");
        if ($mediumSeverity > 0) $this->warn("🟡 Medium Priority: {$mediumSeverity}");
        if ($lowSeverity > 0) $this->info("🟢 Low Priority: {$lowSeverity}");

        $this->info('\nDetailed Issues:');
        foreach ($issues as $issue) {
            $icon = $issue['severity'] === 'high' ? '🔴' : ($issue['severity'] === 'medium' ? '🟡' : '🟢');
            $this->line("{$icon} {$issue['description']}");
        }

        $this->info("\n💡 Recommendation: Run with --fix flag to attempt automatic resolution of fixable issues.");
    }

    private function attemptAutoFix($issues)
    {
        $this->info('\n🔧 ATTEMPTING AUTO-FIX');
        $this->info('========================');

        foreach ($issues as $issue) {
            switch ($issue['type']) {
                case 'missing_enterprise':
                    $this->fixMissingEnterprise();
                    break;
                case 'unresolved_past_appointments':
                    $this->fixPastAppointments();
                    break;
                case 'expired_medications_in_stock':
                    $this->flagExpiredMedications();
                    break;
            }
        }
    }

    private function fixMissingEnterprise()
    {
        $defaultEnterpriseId = 1; // Default hospital enterprise
        $updated = DB::table('admin_users')
            ->whereNull('enterprise_id')
            ->update(['enterprise_id' => $defaultEnterpriseId]);
        
        $this->info("✅ Fixed {$updated} users without enterprise assignment");
    }

    private function fixPastAppointments()
    {
        $updated = DB::table('appointments')
            ->where('appointment_date', '<', Carbon::now())
            ->where('status', 'scheduled')
            ->update(['status' => 'completed']);
        
        $this->info("✅ Updated {$updated} past appointments to completed status");
    }

    private function flagExpiredMedications()
    {
        $updated = DB::table('stock_items')
            ->where('expiry_date', '<', Carbon::now())
            ->where('current_quantity', '>', 0)
            ->update(['notes' => DB::raw("CONCAT(COALESCE(notes, ''), ' [EXPIRED - DO NOT DISPENSE]')")]);
        
        $this->info("✅ Flagged {$updated} expired medications");
    }
}
