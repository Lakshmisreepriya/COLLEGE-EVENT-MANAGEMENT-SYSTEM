<?php
// Start output buffering to prevent header issues
ob_start();

session_start();
require_once 'includes/data_manager.php';
require_once 'includes/auth.php';

// Check if user is logged in and is an admin
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = $_POST['event_id'] ?? 0;
    
    // Delete event registrations first
    $dataManager->delete('event_registrations', ['event_id' => $eventId]);
    
    // Delete event
    $dataManager->delete('events', ['id' => $eventId]);
    
    ob_end_clean();
    header('Location: admin_dashboard.php?success=Event deleted successfully');
    exit();
} else {
    ob_end_clean();
    header('Location: admin_dashboard.php');
    exit();
}
?>
