<?php
// Direct fix for email column issue
try {
    $pdo = new PDO('mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;dbname=hospital', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Checking admin_users table structure...\n";
    
    // Check if email column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM admin_users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Current columns: " . implode(', ', $columns) . "\n\n";
    
    if (!in_array('email', $columns)) {
        echo "Email column missing - adding it now...\n";
        $pdo->exec("ALTER TABLE admin_users ADD COLUMN email VARCHAR(255) NULL AFTER username");
        echo "✅ Email column added successfully!\n";
    } else {
        echo "✅ Email column already exists!\n";
    }
    
    // Add unique index on email if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE admin_users ADD UNIQUE INDEX idx_admin_users_email (email)");
        echo "✅ Added unique index on email column!\n";
    } catch (Exception $e) {
        if (str_contains($e->getMessage(), 'Duplicate key name')) {
            echo "ℹ️ Email index already exists\n";
        } else {
            echo "⚠️ Could not add email index: " . $e->getMessage() . "\n";
        }
    }
    
    // Show updated structure
    echo "\nUpdated admin_users columns:\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM admin_users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>
