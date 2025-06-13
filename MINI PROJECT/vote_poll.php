<?php
// Start output buffering to prevent header issues
ob_start();

session_start();
require_once 'includes/data_manager.php';
require_once 'includes/auth.php';

// Check if user is logged in
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pollId = $_POST['poll_id'] ?? 0;
    $optionId = $_POST['option_id'] ?? 0;
    $userId = $_SESSION['user_id'];
    
    // Check if poll exists and is active
    $poll = $dataManager->selectOne('polls', ['id' => $pollId, 'is_active' => true]);
    if (!$poll) {
        ob_end_clean();
        header('Location: student_dashboard.php?error=Poll not found or inactive');
        exit();
    }
    
    // Check if option exists and belongs to this poll
    $option = $dataManager->selectOne('poll_options', ['id' => $optionId, 'poll_id' => $pollId]);
    if (!$option) {
        ob_end_clean();
        header('Location: student_dashboard.php?error=Invalid option');
        exit();
    }
    
    // Check if already voted
    $existingVote = $dataManager->selectOne('poll_votes', [
        'poll_id' => $pollId,
        'user_id' => $userId
    ]);
    
    if ($existingVote) {
        ob_end_clean();
        header('Location: student_dashboard.php?error=You have already voted in this poll');
        exit();
    }
    
    // Record vote with proper timestamp
    $dataManager->insert('poll_votes', [
        'poll_id' => $pollId,
        'user_id' => $userId,
        'option_id' => $optionId,
        'voted_at' => date('Y-m-d H:i:s')
    ]);
    
    // Update vote count
    $option['votes']++;
    $dataManager->update('poll_options', ['id' => $optionId], ['votes' => $option['votes']]);
    
    ob_end_clean();
    header('Location: student_dashboard.php?success=Vote submitted successfully');
    exit();
} else {
    ob_end_clean();
    header('Location: student_dashboard.php');
    exit();
}
?>
