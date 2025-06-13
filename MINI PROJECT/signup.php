<?php
session_start();
require_once 'includes/data_manager.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: student_dashboard.php');
    }
    exit();
}

// Process signup form
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        // Check if username already exists
        $existingUser = $dataManager->selectOne('users', ['username' => $username]);
        if ($existingUser) {
            $error = 'Username already exists';
        } else {
            // Check if email already exists
            $existingEmail = $dataManager->selectOne('users', ['email' => $email]);
            if ($existingEmail) {
                $error = 'Email already exists';
            } else {
                // Create new user
                $userId = $dataManager->insert('users', [
                    'username' => $username,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => $role
                ]);
                
                $success = 'Account created successfully! You can now login.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System - Sign Up</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="auth-container">
        <form class="auth-form" method="POST" action="signup.php">
            <h2>Create Account</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="student">Student</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-full">Sign Up</button>
            
            <p style="text-align: center; margin-top: 20px;">
                Already have an account? <a href="index.php">Login here</a>
            </p>
        </form>
    </div>
</body>
</html>
