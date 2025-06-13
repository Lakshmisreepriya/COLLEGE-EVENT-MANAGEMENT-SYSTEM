<?php
session_start();
require_once '../config/json_database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$eventId = $input['event_id'];

try {
    // Delete event registrations first
    $db->delete('event_registrations', ['event_id' => $eventId]);
    
    // Delete event
    $deleted = $db->delete('events', ['id' => $eventId]);
    
    if ($deleted) {
        echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete event']);
}
?>
