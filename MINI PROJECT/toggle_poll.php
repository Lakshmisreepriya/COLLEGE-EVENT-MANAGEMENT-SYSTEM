<?php
// Start output buffering to prevent header issues
ob_start();

session_start();
require_once 'includes/data_manager.php';
require_once 'includes/auth.php';

// Check if user is logged in and is an admin
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pollId = $_POST['poll_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    
    if ($action === 'activate') {
        $dataManager->update('polls', ['id' => $pollId], ['is_active' => true]);
        ob_end_clean();
        header('Location: admin_dashboard.php?success=Poll activated successfully');
    } elseif ($action === 'deactivate') {
        $dataManager->update('polls', ['id' => $pollId], ['is_active' => false]);
        ob_end_clean();
        header('Location: admin_dashboard.php?success=Poll deactivated successfully');
    } else {
        ob_end_clean();
        header('Location: admin_dashboard.php');
    }
    exit();
} else {
    ob_end_clean();
    header('Location: admin_dashboard.php');
    exit();
}
?>
