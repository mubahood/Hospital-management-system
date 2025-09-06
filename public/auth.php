<?php
/*
|--------------------------------------------------------------------------
| STANDALONE AUTH HANDLER - NO LARAVEL ROUTING ISSUES!
|--------------------------------------------------------------------------
| This handles authentication directly with the database
*/

// Database connection using correct MAMP settings
$host = 'localhost';
$dbname = 'hospital_1';  // Updated to correct database name
$username = 'root';
$password = 'root';
$socket = '/Applications/MAMP/tmp/mysql/mysql.sock';  // MAMP socket

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;unix_socket=$socket", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        // Check admin users table
        try {
            // Use username field for login
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Start session and set user data
                session_start();
                $_SESSION['admin_user_id'] = $user['id'];
                $_SESSION['admin_user_email'] = $user['username'];
                $_SESSION['admin_user_name'] = $user['name'];
                
                // Redirect to dashboard
                header('Location: dashboard.php');
                exit;
            } else {
                $error = "Invalid email or password";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all fields";
    }
}

// If there's an error, redirect back to login with error
if (isset($error)) {
    header('Location: login.php?error=' . urlencode($error));
    exit;
}

// If no POST data, redirect to login
header('Location: login.php');
exit;
