<?php
// Start output buffering to prevent header issues
ob_start();

session_start();
require_once 'includes/data_manager.php';
require_once 'includes/auth.php';

// Check if user is logged in and is an admin
requireAdmin();

$eventId = $_GET['id'] ?? 0;

// Get event details
$event = $dataManager->selectOne('events', ['id' => $eventId]);
if (!$event) {
    ob_end_clean();
    header('Location: admin_dashboard.php?error=Event not found');
    exit();
}

// Get event registrations with user details
$registrations = $dataManager->select('event_registrations', ['event_id' => $eventId]);
$registeredUsers = [];

foreach ($registrations as $registration) {
    $user = $dataManager->selectOne('users', ['id' => $registration['user_id']]);
    if ($user) {
        $registeredUsers[] = [
            'username' => $user['username'],
            'email' => $user['email'],
            'registered_at' => isset($registration['registered_at']) ? $registration['registered_at'] : 'Unknown'
        ];
    }
}

// Sort by registration date (newest first) - handle unknown dates
usort($registeredUsers, function($a, $b) {
    if ($a['registered_at'] === 'Unknown' || $b['registered_at'] === 'Unknown') {
        return 0;
    }
    return strtotime($b['registered_at']) - strtotime($a['registered_at']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registrations - <?php echo htmlspecialchars($event['title']); ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <header class="dashboard-header">
                <h1>Event Registrations</h1>
                <div>
                    <a href="admin_dashboard.php" class="btn">Back to Dashboard</a>
                </div>
            </header>
            
            <div class="dashboard-content">
                <div class="event-card">
                    <div class="event-card-header">
                        <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                        <div class="event-date"><?php echo htmlspecialchars($event['event_date']); ?></div>
                    </div>
                    <div class="event-card-body">
                        <div class="event-description"><?php echo htmlspecialchars($event['description']); ?></div>
                        <div class="event-location">📍 <?php echo htmlspecialchars($event['location']); ?></div>
                        <div class="event-participants">👥 Capacity: <?php echo count($registeredUsers); ?>/<?php echo $event['max_participants']; ?></div>
                    </div>
                </div>
                
                <h3>Registered Participants (<?php echo count($registeredUsers); ?>)</h3>
                
                <?php if (empty($registeredUsers)): ?>
                    <div style="text-align: center; padding: 40px; background-color: #f8f9fa; border-radius: 10px; margin: 20px 0;">
                        <h4 style="color: #666; margin-bottom: 10px;">No Registrations Yet</h4>
                        <p style="color: #888;">This event hasn't received any registrations yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Registration Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registeredUsers as $index => $user): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php if ($user['registered_at'] !== 'Unknown'): ?>
                                                <?php echo date('M j, Y g:i A', strtotime($user['registered_at'])); ?>
                                            <?php else: ?>
                                                <span style="color: #888; font-style: italic;">Unknown</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="registration-summary">
                        <h4>📊 Registration Summary</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
                            <div>
                                <strong>Total Registrations:</strong><br>
                                <span style="font-size: 1.5em; color: #667eea;"><?php echo count($registeredUsers); ?></span>
                            </div>
                            <div>
                                <strong>Available Spots:</strong><br>
                                <span style="font-size: 1.5em; color: <?php echo (max(0, $event['max_participants'] - count($registeredUsers)) > 0) ? '#28a745' : '#dc3545'; ?>;">
                                    <?php echo max(0, $event['max_participants'] - count($registeredUsers)); ?>
                                </span>
                            </div>
                            <div>
                                <strong>Event Status:</strong><br>
                                <?php if (count($registeredUsers) >= $event['max_participants']): ?>
                                    <span style="color: #dc3545; font-weight: bold; font-size: 1.2em;">🔴 FULL</span>
                                <?php elseif (count($registeredUsers) > ($event['max_participants'] * 0.8)): ?>
                                    <span style="color: #ffc107; font-weight: bold; font-size: 1.2em;">🟡 ALMOST FULL</span>
                                <?php else: ?>
                                    <span style="color: #28a745; font-weight: bold; font-size: 1.2em;">🟢 OPEN</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <strong>Fill Rate:</strong><br>
                                <span style="font-size: 1.5em; color: #667eea;">
                                    <?php echo round((count($registeredUsers) / $event['max_participants']) * 100, 1); ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px; padding: 20px; background-color: #e7f3ff; border-radius: 8px; border-left: 4px solid #667eea;">
                        <h4>📋 Event Details</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 10px;">
                            <div>
                                <strong>Event Date:</strong><br>
                                <?php echo date('F j, Y g:i A', strtotime($event['event_date'])); ?>
                            </div>
                            <div>
                                <strong>Location:</strong><br>
                                <?php echo htmlspecialchars($event['location']); ?>
                            </div>
                            <div>
                                <strong>Created:</strong><br>
                                <?php echo date('M j, Y', strtotime($event['created_at'])); ?>
                            </div>
                            <div>
                                <strong>Max Capacity:</strong><br>
                                <?php echo $event['max_participants']; ?> participants
                            </div>
                        </div>
                    </div>
                    
                    <?php if (count($registeredUsers) > 0): ?>
                        <div style="margin-top: 20px; padding: 15px; background-color: #f0f8f0; border-radius: 8px;">
                            <h4>👥 Participant List (for export)</h4>
                            <div style="background: white; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 0.9em; margin-top: 10px;">
                                <?php foreach ($registeredUsers as $index => $user): ?>
                                    <?php echo ($index + 1) . ". " . htmlspecialchars($user['username']) . " (" . htmlspecialchars($user['email']) . ")"; ?><br>
                                <?php endforeach; ?>
                            </div>
                            <p style="margin-top: 10px; color: #666; font-size: 0.9em;">
                                💡 You can copy the above list for external use or attendance tracking.
                            </p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
