<?php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'event_management';

if (!extension_loaded('mysqli')) {
    die('MySQLi extension is required but not installed.');
}

try {
    // Connect to MySQL server (without selecting a database)
    $mysqli = new mysqli($host, $username, $password);
    
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($mysqli->query($sql)) {
        echo "Database created successfully<br>";
    } else {
        die("Error creating database: " . $mysqli->error);
    }
    
    // Select the database
    $mysqli->select_db($dbname);
    
    // Create tables
    $tables = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('student', 'admin') DEFAULT 'student',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            event_date DATETIME NOT NULL,
            location VARCHAR(200),
            max_participants INT DEFAULT 100,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id)
        )",
        
        "CREATE TABLE IF NOT EXISTS event_registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_id INT,
            user_id INT,
            registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_registration (event_id, user_id)
        )",
        
        "CREATE TABLE IF NOT EXISTS polls (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            created_by INT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id)
        )",
        
        "CREATE TABLE IF NOT EXISTS poll_options (
            id INT AUTO_INCREMENT PRIMARY KEY,
            poll_id INT,
            option_text VARCHAR(200) NOT NULL,
            votes INT DEFAULT 0,
            FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
        )",
        
        "CREATE TABLE IF NOT EXISTS poll_votes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            poll_id INT,
            user_id INT,
            option_id INT,
            voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (option_id) REFERENCES poll_options(id) ON DELETE CASCADE,
            UNIQUE KEY unique_vote (poll_id, user_id)
        )"
    ];
    
    foreach ($tables as $sql) {
        if ($mysqli->query($sql)) {
            echo "Table created successfully<br>";
        } else {
            echo "Error creating table: " . $mysqli->error . "<br>";
        }
    }
    
    // Insert default users
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $studentPassword = password_hash('student123', PASSWORD_DEFAULT);
    
    $insertUsers = [
        "INSERT IGNORE INTO users (username, email, password, role) VALUES ('admin', 'admin@example.com', '$adminPassword', 'admin')",
        "INSERT IGNORE INTO users (username, email, password, role) VALUES ('student1', 'student1@example.com', '$studentPassword', 'student')"
    ];
    
    foreach ($insertUsers as $sql) {
        if ($mysqli->query($sql)) {
            echo "Default user created<br>";
        } else {
            echo "Error creating user: " . $mysqli->error . "<br>";
        }
    }
    
    $mysqli->close();
    
    echo "<br><strong>Database setup completed successfully!</strong><br>";
    echo "<a href='index.html'>Go to Login Page</a><br>";
    echo "<a href='setup_check.php'>Run Setup Check Again</a>";
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
