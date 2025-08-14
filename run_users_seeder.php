<?php
require_once '/Applications/MAMP/htdocs/hospital/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once '/Applications/MAMP/htdocs/hospital/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\ComprehensiveUsersSeeder;
use Illuminate\Support\Facades\Artisan;

try {
    echo "🚀 Running Comprehensive Users Seeder...\n\n";
    
    // Run using Artisan directly
    Artisan::call('db:seed', ['--class' => 'ComprehensiveUsersSeeder']);
    
    echo Artisan::output();
    echo "\n🎉 Seeder completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>
