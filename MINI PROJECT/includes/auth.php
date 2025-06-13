<?php
// Remove session_start() from here

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

function logout() {
    session_start();
    session_destroy();
    header('Location: index.php');
    exit();
}
?>
