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
    $pollData = [
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'created_by' => $_SESSION['user_id'],
        'is_active' => true
    ];
    
    $pollId = $db->insert('polls', $pollData);
    
    // Insert poll options
    $options = $_POST['options'];
    foreach ($options as $option) {
        if (!empty(trim($option))) {
            $db->insert('poll_options', [
                'poll_id' => $pollId,
                'option_text' => trim($option),
                'votes' => 0
            ]);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Poll created successfully', 'id' => $pollId]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to create poll']);
}
?>
