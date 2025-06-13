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
    
    // Delete poll votes first
    $dataManager->delete('poll_votes', ['poll_id' => $pollId]);
    
    // Delete poll options
    $dataManager->delete('poll_options', ['poll_id' => $pollId]);
    
    // Delete poll
    $dataManager->delete('polls', ['id' => $pollId]);
    
    ob_end_clean();
    header('Location: admin_dashboard.php?success=Poll deleted successfully');
    exit();
} else {
    ob_end_clean();
    header('Location: admin_dashboard.php');
    exit();
}
?>
