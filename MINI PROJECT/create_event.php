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
    $event_date = $_POST['event_date'] ?? '';
    $location = $_POST['location'] ?? '';
    $max_participants = $_POST['max_participants'] ?? 100;
    
    if (empty($title) || empty($description) || empty($event_date) || empty($location)) {
        $error = 'Please fill in all required fields';
    } else {
        $eventId = $dataManager->insert('events', [
            'title' => $title,
            'description' => $description,
            'event_date' => $event_date,
            'location' => $location,
            'max_participants' => (int)$max_participants,
            'created_by' => $_SESSION['user_id']
        ]);
        
        // Create notification for all students
        require_once 'includes/notification_manager.php';
        $notificationManager = new NotificationManager($dataManager);

        $notificationManager->createNotificationForAllStudents(
            'event',
            'New Event: ' . $title,
            'A new event "' . $title . '" has been created. Check it out and register if interested!',
            $eventId
        );

        // Clean output buffer before redirect
        ob_end_clean();
        header('Location: admin_dashboard.php?success=Event created successfully');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - Event Management</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <header class="dashboard-header">
                <h1>Create New Event</h1>
                <div>
                    <a href="admin_dashboard.php" class="btn">Back to Dashboard</a>
                </div>
            </header>
            
            <div class="dashboard-content">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <div class="dashboard-card">
                    <form method="post" action="create_event.php">
                        <div class="form-group">
                            <label for="title">Event Title:</label>
                            <input type="text" id="title" name="title" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="event_date">Event Date:</label>
                            <input type="datetime-local" id="event_date" name="event_date" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="location">Location:</label>
                            <input type="text" id="location" name="location" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="max_participants">Maximum Participants:</label>
                            <input type="number" id="max_participants" name="max_participants" class="form-control" min="1" value="100" required>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-full">Create Event</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
