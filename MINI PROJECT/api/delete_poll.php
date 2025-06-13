<?php
session_start();
require_once '../config/json_database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$pollId = $input['poll_id'];

try {
    // Delete poll votes first
    $db->delete('poll_votes', ['poll_id' => $pollId]);
    
    // Delete poll options
    $db->delete('poll_options', ['poll_id' => $pollId]);
    
    // Delete poll
    $deleted = $db->delete('polls', ['id' => $pollId]);
    
    if ($deleted) {
        echo json_encode(['success' => true, 'message' => 'Poll deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Poll not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete poll']);
}
?>
