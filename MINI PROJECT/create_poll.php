<?php
// Start output buffering to prevent header issues
ob_start();

session_start();
require_once 'includes/data_manager.php';
require_once 'includes/auth.php';

// Check if user is logged in and is an admin
requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $options = $_POST['options'] ?? [];
    
    if (empty($title) || empty($description) || count($options) < 2) {
        $error = 'Please fill in all required fields and provide at least 2 options';
    } else {
        // Create poll
        $pollId = $dataManager->insert('polls', [
            'title' => $title,
            'description' => $description,
            'created_by' => $_SESSION['user_id'],
            'is_active' => true
        ]);
        
        // Create poll options
        foreach ($options as $optionText) {
            if (!empty(trim($optionText))) {
                $dataManager->insert('poll_options', [
                    'poll_id' => $pollId,
                    'option_text' => trim($optionText),
                    'votes' => 0
                ]);
            }
        }
        
        // Create notification for all students
        require_once 'includes/notification_manager.php';
        $notificationManager = new NotificationManager($dataManager);

        $notificationManager->createNotificationForAllStudents(
            'poll',
            'New Poll: ' . $title,
            'A new poll "' . $title . '" is now available. Share your opinion by voting!',
            $pollId
        );
        
        // Clean output buffer before redirect
        ob_end_clean();
        header('Location: admin_dashboard.php?success=Poll created successfully');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Poll - Event Management</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .option-container {
            margin-bottom: 10px;
        }
        .add-option-btn {
            background-color: #4481eb;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <header class="dashboard-header">
                <h1>Create New Poll</h1>
                <div>
                    <a href="admin_dashboard.php" class="btn">Back to Dashboard</a>
                </div>
            </header>
            
            <div class="dashboard-content">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <div class="dashboard-card">
                    <form method="post" action="create_poll.php">
                        <div class="form-group">
                            <label for="title">Poll Title:</label>
                            <input type="text" id="title" name="title" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Options:</label>
                            <div id="options-container">
                                <div class="option-container">
                                    <input type="text" name="options[]" class="form-control" placeholder="Option 1" required>
                                </div>
                                <div class="option-container">
                                    <input type="text" name="options[]" class="form-control" placeholder="Option 2" required>
                                </div>
                            </div>
                            <button type="button" id="add-option" class="add-option-btn">Add Option</button>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-full">Create Poll</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addOptionBtn = document.getElementById('add-option');
            const optionsContainer = document.getElementById('options-container');
            let optionCount = 2;
            
            addOptionBtn.addEventListener('click', function() {
                optionCount++;
                const optionDiv = document.createElement('div');
                optionDiv.className = 'option-container';
                optionDiv.innerHTML = `<input type="text" name="options[]" class="form-control" placeholder="Option ${optionCount}" required>`;
                optionsContainer.appendChild(optionDiv);
            });
        });
    </script>
</body>
</html>
