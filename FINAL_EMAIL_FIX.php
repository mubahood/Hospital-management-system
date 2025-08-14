<?php
// FINAL FIX: Add email column to admin_users table
// Run this script to fix the email column issue once and for all

$host = 'localhost';
$dbname = 'hospital';
$username = 'root';
$password = 'root';
$socket = '/Applications/MAMP/tmp/mysql/mysql.sock';

try {
    // Connect using socket (MAMP default)
    $pdo = new PDO("mysql:unix_socket=$socket;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to hospital database successfully!\n\n";
    
    // Check current admin_users structure
    echo "=== CURRENT ADMIN_USERS STRUCTURE ===\n";
    $stmt = $pdo->query("DESCRIBE admin_users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $emailExists = false;
    foreach ($columns as $column) {
        echo "{$column['Field']} ({$column['Type']})\n";
        if ($column['Field'] === 'email') {
            $emailExists = true;
        }
    }
    
    if (!$emailExists) {
        echo "\n❌ Email column is missing! Adding it now...\n";
        $pdo->exec("ALTER TABLE admin_users ADD COLUMN email VARCHAR(255) NULL AFTER username");
        echo "✅ Email column added successfully!\n";
        
        // Add unique index
        try {
            $pdo->exec("ALTER TABLE admin_users ADD UNIQUE INDEX idx_email (email)");
            echo "✅ Unique index on email added!\n";
        } catch (Exception $e) {
            echo "ℹ️ Index may already exist: " . $e->getMessage() . "\n";
        }
    } else {
        echo "\n✅ Email column already exists!\n";
    }
    
    echo "\n=== UPDATED ADMIN_USERS STRUCTURE ===\n";
    $stmt = $pdo->query("DESCRIBE admin_users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "{$column['Field']} ({$column['Type']})\n";
    }
    
    echo "\n🎉 ADMIN_USERS TABLE IS NOW READY!\n";
    echo "You can now run: php artisan db:seed --class=HospitalDummyContentSeeder\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    
    // Try alternative connection methods
    echo "\nTrying alternative connection...\n";
    try {
        $pdo = new PDO("mysql:host=localhost;port=8889;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ Connected via port 8889!\n";
        // Repeat the same operations...
    } catch (Exception $e2) {
        echo "❌ Alternative connection failed: " . $e2->getMessage() . "\n";
        echo "\nPlease manually run this SQL command in your database:\n";
        echo "ALTER TABLE admin_users ADD COLUMN email VARCHAR(255) NULL AFTER username;\n";
    }
}
?>
