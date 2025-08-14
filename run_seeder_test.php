<?php
// Test if we can run the seeder now
require_once '/Applications/MAMP/htdocs/hospital/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once '/Applications/MAMP/htdocs/hospital/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\HospitalDummyContentSeeder;
use Illuminate\Support\Facades\Artisan;

try {
    echo "🏥 Testing Hospital Dummy Content Seeder...\n\n";
    
    // Run the seeder using Artisan
    Artisan::call('db:seed', ['--class' => 'HospitalDummyContentSeeder']);
    
    echo "\n✅ Seeder completed successfully!\n";
    echo Artisan::output();
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>
