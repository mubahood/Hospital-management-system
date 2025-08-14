<?php
try {
    $pdo = new PDO('mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;dbname=hospital', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== USERS TABLE STRUCTURE ===\n";
    $stmt = $pdo->query('SHOW CREATE TABLE users');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $result['Create Table'] . "\n\n";
    
    echo "=== ADMIN_USERS TABLE STRUCTURE ===\n";
    $stmt = $pdo->query('SHOW CREATE TABLE admin_users');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $result['Create Table'] . "\n";
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>
