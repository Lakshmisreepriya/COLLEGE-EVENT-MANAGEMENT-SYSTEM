<?php
session_start();
require_once 'includes/data_manager.php';
require_once 'includes/auth.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: index.php');
    exit();
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get all events
$events = $dataManager->select('events');

// Get all polls
$polls = $dataManager->select('polls');

// Get all users
$users = $dataManager->select('users');
$studentCount = 0;
foreach ($users as $user) {
    if ($user['role'] === 'student') {
        $studentCount++;
    }
}

// Get event registrations
$registrations = $dataManager->select('event_registrations');

// Get poll votes
$votes = $dataManager->select('poll_votes');

// Process success message
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Event Management</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <header class="dashboard-header">
                <h1>Admin Dashboard</h1>
                <div>
                    <span>Welcome, <?php echo htmlspecialchars($username); ?></span>
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
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <!-- Dashboard Tab -->
                <div id="dashboard" class="tab-content">
                    <h2>System Overview</h2>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $studentCount; ?></div>
                            <div class="stat-label">Students</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($events); ?></div>
                            <div class="stat-label">Events</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($polls); ?></div>
                            <div class="stat-label">Polls</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($registrations); ?></div>
                            <div class="stat-label">Event Registrations</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($votes); ?></div>
                            <div class="stat-label">Poll Votes</div>
                        </div>
                    </div>
                    
                    <h3>Recent Events</h3>
                    <?php 
                        // Sort events by date (newest first)
                        usort($events, function($a, $b) {
                            return strtotime($b['created_at']) - strtotime($a['created_at']);
                        });
                        
                        // Get only the 3 most recent events
                        $recentEvents = array_slice($events, 0, 3);
                    ?>
                    
                    <?php if (empty($recentEvents)): ?>
                        <p>No events created yet.</p>
                    <?php else: ?>
                        <div class="events-grid">
                            <?php foreach ($recentEvents as $event): ?>
                                <div class="event-card">
                                    <div class="event-card-header">
                                        <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                                        <div class="event-date"><?php echo htmlspecialchars($event['event_date']); ?></div>
                                    </div>
                                    <div class="event-card-body">
                                        <div class="event-description"><?php echo htmlspecialchars($event['description']); ?></div>
                                        <div class="event-location">📍 <?php echo htmlspecialchars($event['location']); ?></div>
                                        <?php
                                            $eventRegistrations = array_filter($registrations, function($reg) use ($event) {
                                                return $reg['event_id'] == $event['id'];
                                            });
                                            
                                            // Get registered user names
                                            $registeredUserNames = [];
                                            foreach ($eventRegistrations as $reg) {
                                                $user = $dataManager->selectOne('users', ['id' => $reg['user_id']]);
                                                if ($user) {
                                                    $registeredUserNames[] = $user['username'];
                                                }
                                            }
                                        ?>
                                        <div class="event-participants">👥 Registrations: <?php echo count($eventRegistrations); ?>/<?php echo $event['max_participants']; ?></div>
                                        <?php if (!empty($registeredUserNames)): ?>
                                            <div style="margin-top: 10px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; font-size: 0.9em;">
                                                <strong>Registered:</strong> <?php echo implode(', ', array_slice($registeredUserNames, 0, 3)); ?>
                                                <?php if (count($registeredUserNames) > 3): ?>
                                                    <span style="color: #666;"> and <?php echo count($registeredUserNames) - 3; ?> more...</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Events Tab -->
                <div id="events" class="tab-content hidden">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2>Manage Events</h2>
                        <a href="create_event.php" class="btn">Create New Event</a>
                    </div>
                    
                    <?php if (empty($events)): ?>
                        <p>No events created yet.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Date</th>
                                        <th>Location</th>
                                        <th>Registrations</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($events as $event): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($event['title']); ?></td>
                                            <td><?php echo htmlspecialchars($event['event_date']); ?></td>
                                            <td><?php echo htmlspecialchars($event['location']); ?></td>
                                            <td>
                                                <?php
                                                    $eventRegistrations = array_filter($registrations, function($reg) use ($event) {
                                                        return $reg['event_id'] == $event['id'];
                                                    });
                                                    echo count($eventRegistrations) . '/' . $event['max_participants'];
                                                ?>
                                            </td>
                                            <td>
                                                <a href="view_event.php?id=<?php echo $event['id']; ?>" class="btn">View Registrations</a>
                                                <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn">Edit</a>
                                                <form method="post" action="delete_event.php" style="display: inline-block;">
                                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this event?')">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Polls Tab -->
                <div id="polls" class="tab-content hidden">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2>Manage Polls</h2>
                        <a href="create_poll.php" class="btn">Create New Poll</a>
                    </div>
                    
                    <?php if (empty($polls)): ?>
                        <p>No polls created yet.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Votes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($polls as $poll): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($poll['title']); ?></td>
                                            <td>
                                                <?php if ($poll['is_active']): ?>
                                                    <span style="color: green;">Active</span>
                                                <?php else: ?>
                                                    <span style="color: red;">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                    $pollVotes = array_filter($votes, function($vote) use ($poll) {
                                                        return $vote['poll_id'] == $poll['id'];
                                                    });
                                                    echo count($pollVotes);
                                                ?>
                                            </td>
                                            <td>
                                                <a href="view_poll.php?id=<?php echo $poll['id']; ?>" class="btn">View Results</a>
                                                <form method="post" action="toggle_poll.php" style="display: inline-block;">
                                                    <input type="hidden" name="poll_id" value="<?php echo $poll['id']; ?>">
                                                    <input type="hidden" name="action" value="<?php echo $poll['is_active'] ? 'deactivate' : 'activate'; ?>">
                                                    <button type="submit" class="btn">
                                                        <?php echo $poll['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                                    </button>
                                                </form>
                                                <form method="post" action="delete_poll.php" style="display: inline-block;">
                                                    <input type="hidden" name="poll_id" value="<?php echo $poll['id']; ?>">
                                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this poll?')">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
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
</body>
</html>
