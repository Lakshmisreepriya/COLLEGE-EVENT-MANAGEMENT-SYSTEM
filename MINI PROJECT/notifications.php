<?php
session_start();
require_once 'includes/data_manager.php';
require_once 'includes/notification_manager.php';
require_once 'includes/auth.php';

// Check if user is logged in
requireLogin();

$notificationManager = new NotificationManager($dataManager);
$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Handle mark as read action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    $notificationManager->markAllAsRead($userId);
    header('Location: notifications.php?success=All notifications marked as read');
    exit();
}

// Get all notifications for user
$notifications = $notificationManager->getUserNotifications($userId, 20);
$unreadCount = $notificationManager->getUnreadCount($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Event Management</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .notification-item {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }
        
        .notification-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .notification-item.unread {
            border-left-color: #ff6b6b;
            background: linear-gradient(135deg, #fff 0%, #f8f9ff 100%);
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .notification-title {
            font-weight: bold;
            color: #333;
            font-size: 1.1em;
        }
        
        .notification-time {
            color: #888;
            font-size: 0.9em;
        }
        
        .notification-message {
            color: #666;
            line-height: 1.5;
            margin-bottom: 10px;
        }
        
        .notification-type {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .notification-type.event {
            background: #e7f3ff;
            color: #0066cc;
        }
        
        .notification-type.poll {
            background: #f0f8f0;
            color: #28a745;
        }
        
        .notification-type.system {
            background: #fff3cd;
            color: #856404;
        }
        
        .unread-badge {
            background: #ff6b6b;
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .notification-actions {
            margin-top: 15px;
        }
        
        .empty-notifications {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }
        
        .empty-notifications i {
            font-size: 4em;
            margin-bottom: 20px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <header class="dashboard-header">
                <h1>📢 Notifications</h1>
                <div>
                    <span>Welcome, <?php echo htmlspecialchars($username); ?></span>
                    <a href="<?php echo isAdmin() ? 'admin_dashboard.php' : 'student_dashboard.php'; ?>" class="btn">Back to Dashboard</a>
                    <a href="logout.php" class="btn">Logout</a>
                </div>
            </header>
            
            <div class="dashboard-content">
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2>Your Notifications 
                        <?php if ($unreadCount > 0): ?>
                            <span class="unread-badge"><?php echo $unreadCount; ?></span>
                        <?php endif; ?>
                    </h2>
                    
                    <?php if ($unreadCount > 0): ?>
                        <form method="post" style="display: inline;">
                            <button type="submit" name="mark_all_read" class="btn">Mark All as Read</button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($notifications)): ?>
                    <div class="empty-notifications">
                        <span style="font-size: 4em;">🔔</span>
                        <h3>No Notifications Yet</h3>
                        <p>You'll see notifications here when admins create new events or polls.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                            <div class="notification-header">
                                <div class="notification-title">
                                    <?php if (!$notification['is_read']): ?>
                                        <span style="color: #ff6b6b; margin-right: 5px;">●</span>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($notification['title']); ?>
                                </div>
                                <div class="notification-time">
                                    <?php echo date('M j, g:i A', strtotime($notification['created_at'])); ?>
                                </div>
                            </div>
                            
                            <div class="notification-message">
                                <?php echo htmlspecialchars($notification['message']); ?>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span class="notification-type <?php echo $notification['type']; ?>">
                                    <?php echo ucfirst($notification['type']); ?>
                                </span>
                                
                                <?php if (!$notification['is_read']): ?>
                                    <button onclick="markAsRead(<?php echo $notification['id']; ?>)" class="btn" style="padding: 5px 15px; font-size: 0.9em;">
                                        Mark as Read
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function markAsRead(notificationId) {
            fetch('api/notifications.php?action=mark_read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'notification_id=' + notificationId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>
