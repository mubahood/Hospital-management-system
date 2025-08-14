<?php
// Test script to check email column and add if missing
try {
    $pdo = new PDO('mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;dbname=hospital', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if email column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM admin_users LIKE 'email'");
    $emailExists = $stmt->rowCount() > 0;
    
    echo "Email column exists: " . ($emailExists ? "YES" : "NO") . "\n";
    
    if (!$emailExists) {
        echo "Adding email column...\n";
        $pdo->exec("ALTER TABLE admin_users ADD COLUMN email VARCHAR(255) NULL AFTER username");
        echo "Email column added successfully!\n";
    }
    
    // Show all columns
    $stmt = $pdo->query("SHOW COLUMNS FROM admin_users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nCurrent admin_users columns:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
