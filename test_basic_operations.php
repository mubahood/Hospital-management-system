<?php
require_once '/Applications/MAMP/htdocs/hospital/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once '/Applications/MAMP/htdocs/hospital/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

try {
    echo "Testing user creation in admin_users table...\n";
    
    // Test direct database insertion
    $userId = DB::table('admin_users')->insertGetId([
        'username' => 'test_' . time(),
        'name' => 'Test User',
        'password' => Hash::make('password'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "✅ User created successfully with ID: $userId\n";
    
    // Test if we can fetch the user
    $user = DB::table('admin_users')->where('id', $userId)->first();
    echo "✅ User fetched: " . $user->name . "\n";
    
    // Clean up
    DB::table('admin_users')->where('id', $userId)->delete();
    echo "✅ Test user deleted\n";
    
    echo "\n🎉 Basic database operations work! Let's test the seeder...\n\n";
    
    // Now run a minimal version of the seeder
    $hospitalId = DB::table('admin_users')->insertGetId([
        'username' => 'hospital_owner',
        'name' => 'Dr. Sarah Mitchell',
        'password' => Hash::make('password123'),
        'avatar' => null,
        'remember_token' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "✅ Hospital owner created with ID: $hospitalId\n";
    
    // Test Enterprise creation
    $enterpriseId = DB::table('enterprises')->insertGetId([
        'type' => 'Hospital',
        'name' => 'Green Valley Medical Center',
        'short_name' => 'GVMC',
        'phone_number' => '+1-555-0123',
        'address' => '123 Medical Drive, Healthcare City',
        'services' => 'Emergency Care, Surgery, Diagnostics',
        'details' => 'A comprehensive medical facility',
        'email' => 'info@greenvalleymedical.com',
        'website' => 'https://greenvalleymedical.com',
        'color' => '#2563eb',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "✅ Enterprise created with ID: $enterpriseId\n";
    
    echo "\n🏥 Hospital Management System is ready!\n";
    echo "You can now run the full seeder: php artisan db:seed --class=HospitalDummyContentSeeder\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>
