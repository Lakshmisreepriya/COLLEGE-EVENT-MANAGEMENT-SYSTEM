<?php
/**
 * Notification Manager
 * Handles creating and managing notifications for users
 */
class NotificationManager {
    private $dataManager;
    
    public function __construct($dataManager) {
        $this->dataManager = $dataManager;
        $this->initializeNotifications();
    }
    
    private function initializeNotifications() {
        // Initialize notifications file if it doesn't exist
        $notifications = $this->dataManager->readData('notifications');
        if (empty($notifications)) {
            $this->dataManager->writeData('notifications', []);
        }
    }
    
    /**
     * Create notification for all students
     */
    public function createNotificationForAllStudents($type, $title, $message, $relatedId = null) {
        // Get all students
        $students = $this->dataManager->select('users', ['role' => 'student']);
        
        foreach ($students as $student) {
            $this->createNotification($student['id'], $type, $title, $message, $relatedId);
        }
    }
    
    /**
     * Create notification for specific user
     */
    public function createNotification($userId, $type, $title, $message, $relatedId = null) {
        $notification = [
            'user_id' => $userId,
            'type' => $type, // 'event', 'poll', 'system'
            'title' => $title,
            'message' => $message,
            'related_id' => $relatedId, // ID of event/poll
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->dataManager->insert('notifications', $notification);
    }
    
    /**
     * Get unread notifications for user
     */
    public function getUnreadNotifications($userId) {
        return $this->dataManager->select('notifications', [
            'user_id' => $userId,
            'is_read' => false
        ]);
    }
    
    /**
     * Get all notifications for user (with limit)
     */
    public function getUserNotifications($userId, $limit = 10) {
        $notifications = $this->dataManager->select('notifications', ['user_id' => $userId]);
        
        // Sort by creation date (newest first)
        usort($notifications, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($notifications, 0, $limit);
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId) {
        $this->dataManager->update('notifications', 
            ['id' => $notificationId], 
            ['is_read' => true]
        );
    }
    
    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead($userId) {
        $notifications = $this->dataManager->select('notifications', ['user_id' => $userId]);
        
        foreach ($notifications as $notification) {
            if (!$notification['is_read']) {
                $this->dataManager->update('notifications', 
                    ['id' => $notification['id']], 
                    ['is_read' => true]
                );
            }
        }
    }
    
    /**
     * Get notification count for user
     */
    public function getUnreadCount($userId) {
        return count($this->getUnreadNotifications($userId));
    }
    
    /**
     * Delete old notifications (older than 30 days)
     */
    public function cleanupOldNotifications() {
        $notifications = $this->dataManager->readData('notifications');
        $cutoffDate = date('Y-m-d H:i:s', strtotime('-30 days'));
        
        $filteredNotifications = array_filter($notifications, function($notification) use ($cutoffDate) {
            return $notification['created_at'] > $cutoffDate;
        });
        
        $this->dataManager->writeData('notifications', array_values($filteredNotifications));
    }
}
?>
