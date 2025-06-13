<?php
session_start();
require_once '../config/json_database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $userId = $_SESSION['user_id'];
    $events = $db->select('events');
    
    // Check registration status for each event
    foreach ($events as &$event) {
        $registration = $db->selectOne('event_registrations', [
            'event_id' => $event['id'],
            'user_id' => $userId
        ]);
        $event['is_registered'] = !empty($registration);
    }
    
    // Sort by event date
    usort($events, function($a, $b) {
        return strtotime($a['event_date']) - strtotime($b['event_date']);
    });
    
    echo json_encode($events);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
