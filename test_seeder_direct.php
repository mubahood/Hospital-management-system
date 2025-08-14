<?php
// Direct test of seeder functionality
require_once '/Applications/MAMP/htdocs/hospital/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once '/Applications/MAMP/htdocs/hospital/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

try {
    echo "Testing database connection...\n";
    
    // Test basic connection
    $result = DB::select('SELECT COUNT(*) as count FROM admin_users');
    echo "Current admin_users count: " . $result[0]->count . "\n";
    
    // Check if email column exists
    try {
        DB::select("SELECT email FROM admin_users LIMIT 1");
        echo "Email column exists ✓\n";
    } catch (Exception $e) {
        if (str_contains($e->getMessage(), "Unknown column 'email'")) {
            echo "Email column missing - adding it...\n";
            DB::statement("ALTER TABLE admin_users ADD COLUMN email VARCHAR(255) NULL AFTER username");
            echo "Email column added ✓\n";
        } else {
            throw $e;
        }
    }
    
    // Test inserting a user
    echo "Testing user insertion...\n";
    $userId = DB::table('admin_users')->insertGetId([
        'username' => 'test_user_' . time(),
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "User created with ID: $userId ✓\n";
    
    // Clean up
    DB::table('admin_users')->where('id', $userId)->delete();
    echo "Test user cleaned up ✓\n";
    
    echo "All tests passed! The seeder should work now.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
