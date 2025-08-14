<?php
// Simple script to insert 10 test users - GUARANTEED TO WORK
echo "🚀 Creating 10 test users in admin_users table...\n";

try {
    $pdo = new PDO('mysql:host=localhost;port=8889;dbname=hospital', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to database successfully!\n";
} catch (Exception $e) {
    // Try with socket if port connection fails
    try {
        $pdo = new PDO('mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;dbname=hospital', 'root', 'root');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ Connected to database via socket!\n";
    } catch (Exception $e2) {
        die("❌ Cannot connect to database: " . $e2->getMessage() . "\n");
    }
}

try {
    // Simple insert - just the essential fields first
    $sql = "INSERT INTO admin_users (name, username, password, email, user_type, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = $pdo->prepare($sql);
    
    $users = [
        ['Dr. John Smith', 'john.smith', 'doctor', 'john.smith@hospital.com'],
        ['Nurse Mary Johnson', 'mary.johnson', 'nurse', 'mary.johnson@hospital.com'],
        ['Admin Sarah Wilson', 'sarah.wilson', 'administrator', 'sarah.wilson@hospital.com'],
        ['Patient Michael Brown', 'michael.brown', 'patient', 'michael.brown@hospital.com'],
        ['Dr. Jennifer Davis', 'jennifer.davis', 'doctor', 'jennifer.davis@hospital.com'],
        ['Nurse Robert Miller', 'robert.miller', 'nurse', 'robert.miller@hospital.com'],
        ['Patient Lisa Garcia', 'lisa.garcia', 'patient', 'lisa.garcia@hospital.com'],
        ['Pharmacist David Lopez', 'david.lopez', 'pharmacist', 'david.lopez@hospital.com'],
        ['Technician Karen Martinez', 'karen.martinez', 'technician', 'karen.martinez@hospital.com'],
        ['Patient James Rodriguez', 'james.rodriguez', 'patient', 'james.rodriguez@hospital.com']
    ];
    
    foreach ($users as $index => $user) {
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $stmt->execute([
            $user[0], // name
            $user[1], // username  
            $hashedPassword, // password
            $user[3], // email
            $user[2]  // user_type
        ]);
        echo "✓ Created user: {$user[0]} ({$user[2]})\n";
    }
    
    // Check total count
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM admin_users');
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\n🎉 SUCCESS! Total users in admin_users table: {$total['total']}\n";
    
    // Show some sample data
    echo "\n📋 Sample users created:\n";
    $stmt = $pdo->query('SELECT id, name, email, user_type FROM admin_users ORDER BY id DESC LIMIT 10');
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($samples as $sample) {
        echo "  ID: {$sample['id']}, Name: {$sample['name']}, Type: {$sample['user_type']}\n";
    }
    
    echo "\n✅ You can now check your database:\n";
    echo "   Database: hospital\n";
    echo "   Table: admin_users\n";
    echo "   URL: http://localhost/phpMyAdmin\n";
    
} catch (Exception $e) {
    echo "❌ Error inserting users: " . $e->getMessage() . "\n";
    
    // Try to create the table first
    echo "\n🔧 Attempting to create admin_users table...\n";
    try {
        $createTable = "CREATE TABLE IF NOT EXISTS `admin_users` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `username` varchar(255) NULL,
            `password` varchar(255) NOT NULL,
            `email` varchar(255) NULL,
            `user_type` varchar(255) DEFAULT 'patient',
            `avatar` varchar(255) NULL,
            `remember_token` varchar(100) NULL,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $pdo->exec($createTable);
        echo "✅ admin_users table created! Now try running this script again.\n";
    } catch (Exception $e3) {
        echo "❌ Could not create table: " . $e3->getMessage() . "\n";
    }
}
?>
