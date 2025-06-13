<?php
session_start();
require_once '../config/json_database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$eventId = $input['event_id'];
$userId = $_SESSION['user_id'];

try {
    // Check if already registered
    $existing = $db->selectOne('event_registrations', [
        'event_id' => $eventId,
        'user_id' => $userId
    ]);
    
    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'Already registered for this event']);
        exit();
    }
    
    // Register for event
    $db->insert('event_registrations', [
        'event_id' => $eventId,
        'user_id' => $userId,
        'registered_at' => date('Y-m-d H:i:s')
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Successfully registered']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Registration failed']);
}
?>
