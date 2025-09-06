<?php
/*
|--------------------------------------------------------------------------
| CREATE ADMIN USER TOOL
|--------------------------------------------------------------------------
| Create a test admin user for login
*/

$host = 'localhost';
$dbname = 'hospital_1';
$username = 'root';
$password = 'root';
$socket = '/Applications/MAMP/tmp/mysql/mysql.sock';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;unix_socket=$socket", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Create Admin User</h1>";
    
    // Check if admin_users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
    $table_exists = $stmt->fetch();
    
    if (!$table_exists) {
        echo "<h2>Creating admin_users table...</h2>";
        
        // Create admin_users table
        $sql = "CREATE TABLE admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(191) NOT NULL UNIQUE,
            name VARCHAR(191) NOT NULL,
            avatar VARCHAR(255) NULL,
            password VARCHAR(255) NOT NULL,
            remember_token VARCHAR(100) NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql);
        echo "<p>✅ admin_users table created successfully!</p>";
    } else {
        echo "<p>✅ admin_users table already exists.</p>";
    }
    
    // Create a test admin user
    $test_username = 'admin@hospital.com';
    $test_password = 'password123';
    $test_name = 'Admin User';
    $hashed_password = password_hash($test_password, PASSWORD_DEFAULT);
    
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
    $stmt->execute([$test_username]);
    $existing_user = $stmt->fetch();
    
    if (!$existing_user) {
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, name, password) VALUES (?, ?, ?)");
        $stmt->execute([$test_username, $test_name, $hashed_password]);
        
        echo "<h2>✅ Test Admin User Created!</h2>";
        echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>Login Credentials:</h3>";
        echo "<p><strong>Email/Username:</strong> $test_username</p>";
        echo "<p><strong>Password:</strong> $test_password</p>";
        echo "</div>";
        
        echo "<p><a href='login.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
    } else {
        echo "<h2>Admin User Already Exists</h2>";
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>Existing Login Credentials:</h3>";
        echo "<p><strong>Email/Username:</strong> $test_username</p>";
        echo "<p><strong>Password:</strong> $test_password</p>";
        echo "</div>";
        
        echo "<p><a href='login.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
    }
    
} catch (PDOException $e) {
    echo "<h1>Error:</h1>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>
