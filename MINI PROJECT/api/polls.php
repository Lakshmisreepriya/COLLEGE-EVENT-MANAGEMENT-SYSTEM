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
    $polls = $db->select('polls', ['is_active' => true]);
    
    // Get options and vote status for each poll
    foreach ($polls as &$poll) {
        // Check if user has voted
        $vote = $db->selectOne('poll_votes', [
            'poll_id' => $poll['id'],
            'user_id' => $userId
        ]);
        $poll['has_voted'] = !empty($vote);
        
        // Get poll options
        $poll['options'] = $db->select('poll_options', ['poll_id' => $poll['id']]);
    }
    
    // Sort by creation date (newest first)
    usort($polls, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    echo json_encode($polls);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
