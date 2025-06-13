<?php
// Start output buffering to prevent header issues
ob_start();

session_start();
require_once 'includes/data_manager.php';
require_once 'includes/auth.php';

// Check if user is logged in and is an admin
requireAdmin();

$pollId = $_GET['id'] ?? 0;

// Get poll details
$poll = $dataManager->selectOne('polls', ['id' => $pollId]);
if (!$poll) {
    ob_end_clean();
    header('Location: admin_dashboard.php?error=Poll not found');
    exit();
}

// Get poll options with vote counts
$options = $dataManager->select('poll_options', ['poll_id' => $pollId]);

// Get all votes for this poll with user details
$votes = $dataManager->select('poll_votes', ['poll_id' => $pollId]);
$totalVotes = count($votes);

// Get user details for voters
$voters = [];
foreach ($votes as $vote) {
    $user = $dataManager->selectOne('users', ['id' => $vote['user_id']]);
    if ($user) {
        $voters[] = [
            'username' => $user['username'],
            'option_id' => $vote['option_id'],
            'voted_at' => isset($vote['voted_at']) ? $vote['voted_at'] : 'Unknown'
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poll Results - <?php echo htmlspecialchars($poll['title']); ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <header class="dashboard-header">
                <h1>Poll Results</h1>
                <div>
                    <a href="admin_dashboard.php" class="btn">Back to Dashboard</a>
                </div>
            </header>
            
            <div class="dashboard-content">
                <div class="poll-card">
                    <div class="poll-title"><?php echo htmlspecialchars($poll['title']); ?></div>
                    <div class="poll-description"><?php echo htmlspecialchars($poll['description']); ?></div>
                    
                    <div class="poll-results">
                        <h3>Results (<?php echo $totalVotes; ?> total votes)</h3>
                        
                        <?php if ($totalVotes > 0): ?>
                            <?php foreach ($options as $option): ?>
                                <?php 
                                    $optionVotes = array_filter($votes, function($vote) use ($option) {
                                        return $vote['option_id'] == $option['id'];
                                    });
                                    $optionVoteCount = count($optionVotes);
                                    $percentage = $totalVotes > 0 ? ($optionVoteCount / $totalVotes) * 100 : 0;
                                ?>
                                <div class="poll-result-item">
                                    <div style="margin-bottom: 10px;">
                                        <strong><?php echo htmlspecialchars($option['option_text']); ?></strong>
                                    </div>
                                    <div class="poll-result-bar">
                                        <div class="poll-result-fill" style="width: <?php echo $percentage; ?>%"></div>
                                        <div class="poll-result-text"><?php echo $optionVoteCount; ?> votes (<?php echo round($percentage, 1); ?>%)</div>
                                    </div>
                                    
                                    <!-- Show voters for this option -->
                                    <?php if ($optionVoteCount > 0): ?>
                                        <div class="voter-list">
                                            <strong>Voters:</strong>
                                            <?php 
                                                $optionVoters = array_filter($voters, function($voter) use ($option) {
                                                    return $voter['option_id'] == $option['id'];
                                                });
                                                $voterNames = array_map(function($voter) {
                                                    return htmlspecialchars($voter['username']);
                                                }, $optionVoters);
                                                echo implode(', ', $voterNames);
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No votes yet for this poll.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="registration-summary">
                        <h4>📊 Poll Information</h4>
                        <p><strong>Status:</strong> <?php echo $poll['is_active'] ? '<span style="color: green;">Active</span>' : '<span style="color: red;">Inactive</span>'; ?></p>
                        <p><strong>Created:</strong> <?php echo htmlspecialchars($poll['created_at']); ?></p>
                        <p><strong>Total Participants:</strong> <?php echo $totalVotes; ?></p>
                        <p><strong>Options Available:</strong> <?php echo count($options); ?></p>
                    </div>
                    
                    <?php if ($totalVotes > 0): ?>
                        <div style="margin-top: 20px;">
                            <h4>📋 Detailed Voting List</h4>
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Voter</th>
                                            <th>Choice</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        // Sort voters by vote time (if available)
                                        usort($voters, function($a, $b) {
                                            if ($a['voted_at'] === 'Unknown' || $b['voted_at'] === 'Unknown') {
                                                return 0;
                                            }
                                            return strtotime($b['voted_at']) - strtotime($a['voted_at']);
                                        });
                                        
                                        foreach ($voters as $index => $voter): 
                                            // Find the option text for this voter
                                            $voterOption = null;
                                            foreach ($options as $option) {
                                                if ($option['id'] == $voter['option_id']) {
                                                    $voterOption = $option['option_text'];
                                                    break;
                                                }
                                            }
                                        ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($voter['username']); ?></td>
                                                <td><?php echo htmlspecialchars($voterOption ?? 'Unknown'); ?></td>
                                                <td><?php echo htmlspecialchars($voter['voted_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
