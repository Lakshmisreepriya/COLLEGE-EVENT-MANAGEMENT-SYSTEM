<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Status - Event Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .status { margin: 10px 0; padding: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .btn { background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
    </style>
</head>
<body>
    <h1>Event Management System - Status</h1>
    
    <?php
    // Check PHP version
    echo "<div class='status " . (version_compare(PHP_VERSION, '7.0.0') >= 0 ? "success" : "error") . "'>";
    echo "<strong>PHP Version:</strong> " . PHP_VERSION . (version_compare(PHP_VERSION, '7.0.0') >= 0 ? " ✅" : " ❌ (Requires 7.0+)");
    echo "</div>";
    
    // Check if data directory exists and is writable
    $dataDir = __DIR__ . '/data/';
    $dataDirExists = is_dir($dataDir);
    $dataDirWritable = $dataDirExists && is_writable($dataDir);
    
    echo "<div class='status " . ($dataDirWritable ? "success" : "error") . "'>";
    echo "<strong>Data Directory:</strong> ";
    if ($dataDirWritable) {
        echo "Ready ✅ ($dataDir)";
    } else {
        echo "Not writable ❌";
        if (!$dataDirExists) {
            echo " (Directory doesn't exist)";
        }
    }
    echo "</div>";
    
    // Check session support
    echo "<div class='status " . (function_exists('session_start') ? "success" : "error") . "'>";
    echo "<strong>Session Support:</strong> " . (function_exists('session_start') ? "Available ✅" : "Not Available ❌");
    echo "</div>";
    
    // Check JSON support
    echo "<div class='status " . (function_exists('json_encode') ? "success" : "error") . "'>";
    echo "<strong>JSON Support:</strong> " . (function_exists('json_encode') ? "Available ✅" : "Not Available ❌");
    echo "</div>";
    
    // Test database functionality
    try {
        require_once 'includes/data_manager.php';
        $testUsers = $dataManager->select('users');
        echo "<div class='status success'>";
        echo "<strong>Database System:</strong> Working ✅ (" . count($testUsers) . " users found)";
        echo "</div>";
        
        // Show data files status
        $files = ['users', 'events', 'event_registrations', 'polls', 'poll_options', 'poll_votes'];
        echo "<div class='status info'>";
        echo "<strong>Data Files:</strong><br>";
        foreach ($files as $file) {
            $filepath = $dataDir . $file . '.json';
            $exists = file_exists($filepath);
            $count = 0;
            if ($exists) {
                $data = json_decode(file_get_contents($filepath), true);
                $count = is_array($data) ? count($data) : 0;
            }
            echo "• $file.json: " . ($exists ? "✅ ($count records)" : "❌ Missing") . "<br>";
        }
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='status error'>";
        echo "<strong>Database System:</strong> Error ❌<br>";
        echo "Error: " . $e->getMessage();
        echo "</div>";
    }
    
    // Overall status
    $allGood = version_compare(PHP_VERSION, '7.0.0') >= 0 && 
               $dataDirWritable && 
               function_exists('session_start') && 
               function_exists('json_encode');
    
    echo "<div class='status " . ($allGood ? "success" : "error") . "'>";
    echo "<h2>" . ($allGood ? "🎉 System Ready!" : "⚠️ Issues Found") . "</h2>";
    if ($allGood) {
        echo "<p>Your Event Management System is ready to use!</p>";
        echo "<a href='index.php' class='btn'>Go to Login Page</a>";
    } else {
        echo "<p>Please fix the issues above before using the system.</p>";
        if (!$dataDirWritable) {
            echo "<p><strong>Fix:</strong> Create the 'data' directory and make it writable:</p>";
            echo "<code>mkdir data && chmod 755 data</code>";
        }
    }
    echo "</div>";
    
    echo "<div class='status info'>";
    echo "<h3>🔑 Default Login Credentials</h3>";
    echo "<strong>Admin:</strong> username: <code>admin</code>, password: <code>admin123</code><br>";
    echo "<strong>Student:</strong> username: <code>student1</code>, password: <code>student123</code>";
    echo "</div>";
    ?>
    
    <div style="margin-top: 20px;">
        <a href="index.php" class="btn">Login Page</a>
        <a href="signup.php" class="btn">Sign Up</a>
        <button onclick="location.reload()" class="btn">Refresh Status</button>
    </div>
</body>
</html>
