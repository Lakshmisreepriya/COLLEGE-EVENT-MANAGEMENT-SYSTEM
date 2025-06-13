<?php
session_start();
require_once 'includes/data_manager.php';
require_once 'includes/auth.php';

// Check if user is logged in and is a student
if (!isLoggedIn() || isAdmin()) {
    header('Location: index.php');
    exit();
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get user's registered events
$registrations = $dataManager->select('event_registrations', ['user_id' => $userId]);
$registeredEventIds = array_column($registrations, 'event_id');

// Get all events
$events = $dataManager->select('events');

// Get active polls
$polls = $dataManager->select('polls', ['is_active' => true]);

// Get user's poll votes
$votes = $dataManager->select('poll_votes', ['user_id' => $userId]);
$votedPollIds = array_column($votes, 'poll_id');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Event Management</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <header class="dashboard-header">
                <h1>Student Dashboard</h1>
                <div>
                    <span>Welcome, <?php echo htmlspecialchars($username); ?></span>
                    <a href="notifications.php" class="btn" style="position: relative; margin-right: 10px;">
                        🔔 Notifications
                        <span id="notification-badge" class="unread-badge" style="position: absolute; top: -5px; right: -5px; display: none;"></span>
                    </a>
                    <a href="logout.php" class="btn">Logout</a>
                </div>
            </header>
            
            <nav class="dashboard-nav">
                <ul class="nav-tabs">
                    <li class="nav-tab active" data-tab="dashboard">Dashboard</li>
                    <li class="nav-tab" data-tab="events">Events</li>
                    <li class="nav-tab" data-tab="polls">Polls</li>
                </ul>
            </nav>
            
            <div class="dashboard-content">
                <?php 
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
                <!-- Dashboard Tab -->
                <div id="dashboard" class="tab-content">
                    <h2>Your Overview</h2>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($registeredEventIds); ?></div>
                            <div class="stat-label">Registered Events</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($votedPollIds); ?></div>
                            <div class="stat-label">Polls Participated</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($events); ?></div>
                            <div class="stat-label">Total Events</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($polls); ?></div>
                            <div class="stat-label">Active Polls</div>
                        </div>
                    </div>
                    
                    <h3>Your Registered Events</h3>
                    <?php if (empty($registeredEventIds)): ?>
                        <p>You haven't registered for any events yet.</p>
                    <?php else: ?>
                        <div class="events-grid">
                            <?php foreach ($events as $event): ?>
                                <?php if (in_array($event['id'], $registeredEventIds)): ?>
                                    <div class="event-card">
                                        <div class="event-card-header">
                                            <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                                            <div class="event-date"><?php echo htmlspecialchars($event['event_date']); ?></div>
                                        </div>
                                        <div class="event-card-body">
                                            <div class="event-description"><?php echo htmlspecialchars($event['description']); ?></div>
                                            <div class="event-location">📍 <?php echo htmlspecialchars($event['location']); ?></div>
                                            <button class="btn btn-success" disabled>Registered</button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Events Tab -->
                <div id="events" class="tab-content hidden">
                    <h2>Available Events</h2>
                    
                    <?php if (empty($events)): ?>
                        <p>No events available at the moment.</p>
                    <?php else: ?>
                        <div class="events-grid">
                            <?php foreach ($events as $event): ?>
                                <div class="event-card">
                                    <div class="event-card-header">
                                        <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                                        <div class="event-date"><?php echo htmlspecialchars($event['event_date']); ?></div>
                                    </div>
                                    <div class="event-card-body">
                                        <div class="event-description"><?php echo htmlspecialchars($event['description']); ?></div>
                                        <div class="event-location">📍 <?php echo htmlspecialchars($event['location']); ?></div>
                                        <div class="event-participants">👥 Max: <?php echo htmlspecialchars($event['max_participants']); ?></div>
                                        
                                        <?php if (in_array($event['id'], $registeredEventIds)): ?>
                                            <button class="btn btn-success" disabled>Registered</button>
                                        <?php else: ?>
                                            <form method="post" action="register_event.php">
                                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                <button type="submit" class="btn">Register</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Polls Tab -->
                <div id="polls" class="tab-content hidden">
                    <h2>Active Polls</h2>
                    
                    <?php if (empty($polls)): ?>
                        <p>No active polls at the moment.</p>
                    <?php else: ?>
                        <?php foreach ($polls as $poll): ?>
                            <div class="poll-card">
                                <div class="poll-title"><?php echo htmlspecialchars($poll['title']); ?></div>
                                <div class="poll-description"><?php echo htmlspecialchars($poll['description']); ?></div>
                                
                                <?php if (in_array($poll['id'], $votedPollIds)): ?>
                                    <!-- Show that user has voted but not the results -->
                                    <div style="padding: 20px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; margin-top: 15px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span style="font-size: 1.5em;">✅</span>
                                            <div>
                                                <strong style="color: #155724;">You have voted in this poll</strong>
                                                <p style="color: #155724; margin: 5px 0 0 0; font-size: 0.9em;">
                                                    Thank you for participating! Results will be shared by the admin.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Show voting form -->
                                    <form method="post" action="vote_poll.php">
                                        <input type="hidden" name="poll_id" value="<?php echo $poll['id']; ?>">
                                        
                                        <div style="margin: 15px 0;">
                                            <strong>Choose your option:</strong>
                                        </div>
                                        
                                        <?php 
                                            $options = $dataManager->select('poll_options', ['poll_id' => $poll['id']]);
                                            foreach ($options as $option): 
                                        ?>
                                            <div class="poll-option">
                                                <input type="radio" name="option_id" value="<?php echo $option['id']; ?>" id="option_<?php echo $option['id']; ?>" required>
                                                <label for="option_<?php echo $option['id']; ?>"><?php echo htmlspecialchars($option['option_text']); ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                        
                                        <button type="submit" class="btn" style="margin-top: 15px;">Submit Vote</button>
                                    </form>
                                    
                                    <div style="margin-top: 15px; padding: 10px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; font-size: 0.9em;">
                                        <strong>📝 Note:</strong> You can only vote once in each poll. Choose carefully!
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.nav-tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Hide all content sections
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.add('hidden');
                    });
                    
                    // Show selected content
                    const tabName = this.getAttribute('data-tab');
                    document.getElementById(tabName).classList.remove('hidden');
                });
            });
        });
    </script>
    <script>
// Check for new notifications every 30 seconds
function checkNotifications() {
    fetch('api/notifications.php?action=unread_count')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notification-badge');
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        })
        .catch(error => console.error('Error checking notifications:', error));
}

// Check immediately and then every 30 seconds
checkNotifications();
setInterval(checkNotifications, 30000);
</script>
</body>
</html>
