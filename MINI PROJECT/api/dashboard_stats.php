<?php
session_start();
require_once '../config/json_database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $stats = [];
    
    // Total events
    $stats['total_events'] = $db->count('events');
    
    // Total active polls
    $stats['total_polls'] = $db->count('polls', ['is_active' => true]);
    
    // Total event registrations
    $stats['total_registrations'] = $db->count('event_registrations');
    
    // Total poll votes
    $stats['total_votes'] = $db->count('poll_votes');
    
    echo json_encode($stats);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
