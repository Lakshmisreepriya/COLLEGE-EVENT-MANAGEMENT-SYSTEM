<?php
session_start();
require_once '../includes/data_manager.php';
require_once '../includes/notification_manager.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$notificationManager = new NotificationManager($dataManager);
$userId = $_SESSION['user_id'];

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get':
        $notifications = $notificationManager->getUserNotifications($userId);
        echo json_encode($notifications);
        break;
        
    case 'unread_count':
        $count = $notificationManager->getUnreadCount($userId);
        echo json_encode(['count' => $count]);
        break;
        
    case 'mark_read':
        $notificationId = $_POST['notification_id'] ?? 0;
        $notificationManager->markAsRead($notificationId);
        echo json_encode(['success' => true]);
        break;
        
    case 'mark_all_read':
        $notificationManager->markAllAsRead($userId);
        echo json_encode(['success' => true]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>
