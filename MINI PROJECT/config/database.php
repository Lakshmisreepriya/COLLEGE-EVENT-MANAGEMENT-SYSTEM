<?php
$host = 'localhost';
$dbname = 'event_management';
$username = 'root';
$password = '';

// Check if PDO MySQL driver is available
if (!extension_loaded('pdo_mysql')) {
    die('Error: PDO MySQL driver is not installed. Please install php-mysql extension.');
}

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
} catch(PDOException $e) {
    // Log the error and show user-friendly message
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please check your configuration and ensure MySQL is running.");
}
?>
