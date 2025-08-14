<?php
// Simple database diagnostic script
echo "🔍 Database Diagnostic Report\n";
echo "===========================\n\n";

try {
    // Connect to database using your .env settings
    $pdo = new PDO('mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;dbname=hospital', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database: hospital\n\n";
    
    // Show all tables
    echo "📋 Available Tables:\n";
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
    echo "\n";
    
    // Check admin_users table specifically
    if (in_array('admin_users', $tables)) {
        echo "✅ admin_users table EXISTS\n";
        
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM admin_users');
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "📊 Records in admin_users: {$count['count']}\n";
        
        // Show some sample data if any exists
        if ($count['count'] > 0) {
            echo "\n📝 Sample records:\n";
            $stmt = $pdo->query('SELECT id, name, email, user_type FROM admin_users LIMIT 5');
            $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($samples as $sample) {
                echo "  ID: {$sample['id']}, Name: {$sample['name']}, Email: {$sample['email']}, Type: {$sample['user_type']}\n";
            }
        }
        
        // Check table structure
        echo "\n🏗️ Table Structure (first 10 columns):\n";
        $stmt = $pdo->query('DESCRIBE admin_users');
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = 0;
        foreach ($columns as $column) {
            if ($count++ < 10) {
                echo "  - {$column['Field']} ({$column['Type']})\n";
            }
        }
        if (count($columns) > 10) {
            echo "  ... and " . (count($columns) - 10) . " more columns\n";
        }
        
    } else {
        echo "❌ admin_users table does NOT exist!\n";
        echo "   Available tables are: " . implode(', ', $tables) . "\n";
    }
    
    // Check users table too
    echo "\n";
    if (in_array('users', $tables)) {
        echo "ℹ️ users table also exists\n";
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "📊 Records in users: {$count['count']}\n";
    } else {
        echo "ℹ️ users table does not exist\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "💡 Possible issues:\n";
    echo "   - MAMP is not running\n";
    echo "   - Database 'hospital' doesn't exist\n";
    echo "   - Connection settings are wrong\n";
}

echo "\n🎯 Next Steps:\n";
echo "1. Make sure MAMP is running\n";
echo "2. Check if 'hospital' database exists in phpMyAdmin\n";
echo "3. Run migrations if admin_users table is missing\n";
echo "4. Try the user creation script again\n";
?>
