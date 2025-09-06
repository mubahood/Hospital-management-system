<?php
/*
|--------------------------------------------------------------------------
| DATABASE DEBUG TOOL
|--------------------------------------------------------------------------
| Check what tables and admin users exist
*/

$host = 'localhost';
$dbname = 'hospital_1';
$username = 'root';
$password = 'root';
$socket = '/Applications/MAMP/tmp/mysql/mysql.sock';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;unix_socket=$socket", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Database Debug Tool</h1>";
    
    // Show all tables
    echo "<h2>Available Tables:</h2>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Check admin_users table structure
    if (in_array('admin_users', $tables)) {
        echo "<h2>admin_users Table Structure:</h2>";
        $stmt = $pdo->query("DESCRIBE admin_users");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show existing admin users
        echo "<h2>Existing Admin Users:</h2>";
        $stmt = $pdo->query("SELECT * FROM admin_users LIMIT 5");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($users) {
            echo "<table border='1'>";
            $first = true;
            foreach ($users as $user) {
                if ($first) {
                    echo "<tr>";
                    foreach (array_keys($user) as $key) {
                        echo "<th>$key</th>";
                    }
                    echo "</tr>";
                    $first = false;
                }
                echo "<tr>";
                foreach ($user as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No admin users found.</p>";
        }
    } else {
        echo "<p>admin_users table not found!</p>";
        
        // Check for other admin tables
        $admin_tables = array_filter($tables, function($table) {
            return strpos(strtolower($table), 'admin') !== false;
        });
        
        if ($admin_tables) {
            echo "<h2>Found Admin-related Tables:</h2>";
            foreach ($admin_tables as $table) {
                echo "<h3>$table</h3>";
                $stmt = $pdo->query("DESCRIBE $table");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo "<table border='1'>";
                echo "<tr><th>Field</th><th>Type</th></tr>";
                foreach ($columns as $column) {
                    echo "<tr><td>{$column['Field']}</td><td>{$column['Type']}</td></tr>";
                }
                echo "</table>";
                
                // Show sample data
                $stmt = $pdo->query("SELECT * FROM $table LIMIT 3");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($rows) {
                    echo "<h4>Sample Data:</h4>";
                    echo "<table border='1'>";
                    $first = true;
                    foreach ($rows as $row) {
                        if ($first) {
                            echo "<tr>";
                            foreach (array_keys($row) as $key) {
                                echo "<th>$key</th>";
                            }
                            echo "</tr>";
                            $first = false;
                        }
                        echo "<tr>";
                        foreach ($row as $value) {
                            echo "<td>" . htmlspecialchars($value) . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            }
        }
    }
    
} catch (PDOException $e) {
    echo "<h1>Error:</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
