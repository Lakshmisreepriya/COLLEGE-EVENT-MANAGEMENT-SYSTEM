<?php
$host = 'localhost';
$dbname = 'event_management';
$username = 'root';
$password = '';

// Check if MySQLi extension is loaded
if (!extension_loaded('mysqli')) {
    die('Error: MySQLi extension is not installed. Please install php-mysqli extension.');
}

// Create connection
$mysqli = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Set charset
$mysqli->set_charset("utf8mb4");

// Helper function to execute prepared statements
function executeQuery($mysqli, $sql, $params = [], $types = '') {
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    return $stmt;
}

// Helper function to fetch all results
function fetchAll($mysqli, $sql, $params = [], $types = '') {
    $stmt = executeQuery($mysqli, $sql, $params, $types);
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Helper function to fetch single result
function fetchOne($mysqli, $sql, $params = [], $types = '') {
    $stmt = executeQuery($mysqli, $sql, $params, $types);
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
?>
