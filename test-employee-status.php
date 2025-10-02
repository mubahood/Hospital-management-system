<?php

/**
 * Test script to check employee status values
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

try {
    echo "=== Testing Employee Status Values ===\n\n";
    
    // Get all users with user_type = 'employee'
    $employees = User::where('user_type', 'employee')
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    
    echo "Found " . $employees->count() . " employees\n\n";
    
    if ($employees->count() > 0) {
        echo "Sample employees:\n";
        echo "ID | Name | Email | Status | Status Type\n";
        echo "---|------|-------|--------|------------\n";
        
        foreach ($employees as $emp) {
            $statusType = gettype($emp->status);
            echo "{$emp->id} | {$emp->first_name} {$emp->last_name} | {$emp->email} | {$emp->status} | {$statusType}\n";
        }
        
        echo "\n=== Status Value Analysis ===\n";
        $statusValues = $employees->pluck('status')->unique()->toArray();
        echo "Unique status values found: " . json_encode($statusValues) . "\n";
        
        echo "\n=== Active Employees (status = 1) ===\n";
        $activeCount = User::where('user_type', 'employee')->where('status', 1)->count();
        echo "Count: {$activeCount}\n";
        
        echo "\n=== Inactive Employees (status = 0) ===\n";
        $inactiveCount = User::where('user_type', 'employee')->where('status', 0)->count();
        echo "Count: {$inactiveCount}\n";
        
        echo "\n=== Employees with 'Active' string ===\n";
        $activeStringCount = User::where('user_type', 'employee')->where('status', 'Active')->count();
        echo "Count: {$activeStringCount}\n";
        
    } else {
        echo "No employees found!\n";
        
        // Check all users
        echo "\n=== Checking all users ===\n";
        $allUsers = User::limit(10)->get();
        echo "Found " . $allUsers->count() . " total users\n\n";
        
        if ($allUsers->count() > 0) {
            echo "Sample users:\n";
            echo "ID | Name | User Type | Status\n";
            echo "---|------|-----------|-------\n";
            
            foreach ($allUsers as $user) {
                echo "{$user->id} | {$user->first_name} {$user->last_name} | {$user->user_type} | {$user->status}\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
