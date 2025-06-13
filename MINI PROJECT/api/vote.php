<?php
session_start();
require_once '../config/json_database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$pollId = $input['poll_id'];
$optionId = $input['option_id'];
$userId = $_SESSION['user_id'];

try {
    // Check if already voted
    $existing = $db->selectOne('poll_votes', [
        'poll_id' => $pollId,
        'user_id' => $userId
    ]);
    
    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'You have already voted in this poll']);
        exit();
    }
    
    // Insert vote
    $db->insert('poll_votes', [
        'poll_id' => $pollId,
        'user_id' => $userId,
        'option_id' => $optionId,
        'voted_at' => date('Y-m-d H:i:s')
    ]);
    
    // Update vote count
    $options = $db->select('poll_options');
    foreach ($options as &$option) {
        if ($option['id'] == $optionId) {
            $option['votes']++;
            break;
        }
    }
    $db->writeData('poll_options', $options);
    
    echo json_encode(['success' => true, 'message' => 'Vote submitted successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Vote submission failed']);
}
?>
