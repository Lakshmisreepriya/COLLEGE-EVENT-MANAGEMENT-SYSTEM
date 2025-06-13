<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Check - Event Management System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .check { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .code { background-color: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
        .btn { background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
    <h1>Event Management System - Setup Check</h1>
    
    <?php
    echo "<h2>PHP Extensions Check</h2>";
    
    // Check PHP version
    echo "<div class='check " . (version_compare(PHP_VERSION, '7.4.0') >= 0 ? "success" : "error") . "'>";
    echo "PHP Version: " . PHP_VERSION . (version_compare(PHP_VERSION, '7.4.0') >= 0 ? " ✓" : " ✗ (Requires 7.4+)");
    echo "</div>";
    
    // Check PDO
    echo "<div class='check " . (class_exists('PDO') ? "success" : "error") . "'>";
    echo "PDO Extension: " . (class_exists('PDO') ? "Available ✓" : "Not Available ✗");
    echo "</div>";
    
    // Check SQLite
    $sqliteAvailable = class_exists('PDO') && in_array('sqlite', PDO::getAvailableDrivers());
    echo "<div class='check " . ($sqliteAvailable ? "success" : "warning") . "'>";
    echo "SQLite PDO Driver: " . ($sqliteAvailable ? "Available ✓ (Recommended)" : "Not Available");
    echo "</div>";
    
    // Check PDO MySQL
    $mysqlAvailable = extension_loaded('pdo_mysql');
    echo "<div class='check " . ($mysqlAvailable ? "success" : "warning") . "'>";
    echo "PDO MySQL Driver: " . ($mysqlAvailable ? "Available ✓" : "Not Available (Optional)");
    echo "</div>";
    
    // Check session support
    echo "<div class='check " . (function_exists('session_start') ? "success" : "error") . "'>";
    echo "Session Support: " . (function_exists('session_start') ? "Available ✓" : "Not Available ✗");
    echo "</div>";
    
    echo "<h2>Database Status</h2>";
    
    // Test SQLite database
    if ($sqliteAvailable) {
        try {
            $database_file = __DIR__ . '/data/event_management.db';
            $data_dir = dirname($database_file);
            
            if (!is_dir($data_dir)) {
                mkdir($data_dir, 0755, true);
            }
            
            $pdo = new PDO("sqlite:$database_file");
            echo "<div class='check success'>SQLite Database: Ready ✓</div>";
            
            // Check if tables exist
            $tables = ['users', 'events', 'event_registrations', 'polls', 'poll_options', 'poll_votes'];
            $existing_tables = [];
            
            foreach ($tables as $table) {
                $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'");
                if ($result && $result->fetch()) {
                    $existing_tables[] = $table;
                }
            }
            
            if (count($existing_tables) == count($tables)) {
                echo "<div class='check success'>All required tables: Present ✓</div>";
                
                // Check for default users
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                $userCount = $stmt->fetch()['count'];
                echo "<div class='check " . ($userCount > 0 ? "success" : "warning") . "'>";
                echo "Users in database: $userCount " . ($userCount > 0 ? "✓" : "(No users found)");
                echo "</div>";
                
            } else {
                echo "<div class='check warning'>Tables found: " . count($existing_tables) . "/" . count($tables) . " (Will be created automatically)</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='check error'>SQLite Database Error: " . $e->getMessage() . "</div>";
        }
    }
    
    echo "<h2>System Status</h2>";
    
    if ($sqliteAvailable) {
        echo "<div class='check success'>";
        echo "<h3>✅ System Ready!</h3>";
        echo "<p>Your system is ready to use with SQLite database (no MySQL required).</p>";
        echo "<a href='index.html' class='btn'>Go to Login Page</a>";
        echo "</div>";
    } elseif ($mysqlAvailable) {
        echo "<div class='check info'>";
        echo "<h3>MySQL Available</h3>";
        echo "<p>You can use MySQL database. Make sure to configure the database settings.</p>";
        echo "<a href='create_database.php' class='btn'>Setup MySQL Database</a>";
        echo "</div>";
    } else {
        echo "<div class='check error'>";
        echo "<h3>❌ No Database Driver Available</h3>";
        echo "<p>You need either SQLite or MySQL PDO drivers.</p>";
        echo "</div>";
    }
    
    echo "<h2>Default Login Credentials</h2>";
    echo "<div class='check info'>";
    echo "<strong>Admin:</strong> username: <code>admin</code>, password: <code>admin123</code><br>";
    echo "<strong>Student:</strong> username: <code>student1</code>, password: <code>student123</code>";
    echo "</div>";
    
    if (!$sqliteAvailable && !$mysqlAvailable) {
        echo "<h2>Installation Instructions</h2>";
        echo "<div class='check error'>";
        echo "<h3>Install Database Extensions</h3>";
        echo "<div class='code'>";
        echo "<strong>For Ubuntu/Debian:</strong><br>";
        echo "sudo apt-get update<br>";
        echo "sudo apt-get install php-sqlite3 php-mysql<br>";
        echo "sudo systemctl restart apache2<br><br>";
        
        echo "<strong>For CentOS/RHEL:</strong><br>";
        echo "sudo yum install php-pdo php-mysql<br>";
        echo "sudo systemctl restart httpd<br><br>";
        
        echo "<strong>For Windows (XAMPP):</strong><br>";
        echo "1. Open php.ini file<br>";
        echo "2. Uncomment these lines:<br>";
        echo "   extension=pdo_sqlite<br>";
        echo "   extension=pdo_mysql<br>";
        echo "3. Restart Apache<br>";
        echo "</div>";
        echo "</div>";
    }
    ?>
</body>
</html>
