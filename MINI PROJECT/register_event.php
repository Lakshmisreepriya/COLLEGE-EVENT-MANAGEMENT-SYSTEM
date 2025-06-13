<?php
// Start output buffering to prevent header issues
ob_start();

session_start();
require_once 'includes/data_manager.php';
require_once 'includes/auth.php';

// Check if user is logged in
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = $_POST['event_id'] ?? 0;
    $userId = $_SESSION['user_id'];
    
    // Check if event exists
    $event = $dataManager->selectOne('events', ['id' => $eventId]);
    if (!$event) {
        ob_end_clean();
        header('Location: student_dashboard.php?error=Event not found');
        exit();
    }
    
    // Check if already registered
    $existingRegistration = $dataManager->selectOne('event_registrations', [
        'event_id' => $eventId,
        'user_id' => $userId
    ]);
    
    if ($existingRegistration) {
        ob_end_clean();
        header('Location: student_dashboard.php?error=Already registered for this event');
        exit();
    }
    
    // Check if event is full
    $registrations = $dataManager->select('event_registrations', ['event_id' => $eventId]);
    if (count($registrations) >= $event['max_participants']) {
        ob_end_clean();
        header('Location: student_dashboard.php?error=Event is full');
        exit();
    }
    
    // Register for event with proper timestamp
    $dataManager->insert('event_registrations', [
        'event_id' => $eventId,
        'user_id' => $userId,
        'registered_at' => date('Y-m-d H:i:s')
    ]);
    
    ob_end_clean();
    header('Location: student_dashboard.php?success=Successfully registered for event');
    exit();
} else {
    ob_end_clean();
    header('Location: student_dashboard.php');
    exit();
}
?>
