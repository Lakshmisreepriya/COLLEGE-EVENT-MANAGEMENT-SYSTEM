<?php
session_start();
require_once 'config/database_mysqli.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        $sql = "SELECT * FROM users WHERE username = ?";
        $user = fetchOne($mysqli, $sql, [$username], 's');
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] == 'admin') {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: student_dashboard.php');
            }
            exit();
        } else {
            header('Location: index.html?error=Invalid username or password');
            exit();
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        header('Location: index.html?error=Database error');
        exit();
    }
}
?>
