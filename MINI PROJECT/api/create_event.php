<?php
session_start();
require_once '../config/json_database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    $eventData = [
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'event_date' => $_POST['event_date'],
        'location' => $_POST['location'],
        'max_participants' => (int)$_POST['max_participants'],
        'created_by' => $_SESSION['user_id']
    ];
    
    $eventId = $db->insert('events', $eventData);
    
    echo json_encode(['success' => true, 'message' => 'Event created successfully', 'id' => $eventId]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to create event']);
}
?>
