<?php
// api/notifications.php
// نظام الإشعارات
session_start();
require_once '../config/database.php';
require_once '../core/Auth.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit;
}

class NotificationManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // إنشاء إشعار جديد
    public function createNotification($user_id, $title, $message, $type = 'info', $related_id = null) {
        $query = "INSERT INTO notifications (user_id, title, message, type, related_id, created_at) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$user_id, $title, $message, $type, $related_id]);
        
        return $this->pdo->lastInsertId();
    }
    
    // الحصول على الإشعارات
    public function getNotifications($user_id, $unread_only = false, $limit = 50) {
        $query = "SELECT * FROM notifications 
                  WHERE user_id = ?";
        
        if ($unread_only) {
            $query .= " AND is_read = 0";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT " . intval($limit);
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$user_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // عدد الإشعارات غير المقروءة
    public function getUnreadCount($user_id) {
        $query = "SELECT COUNT(*) as count FROM notifications 
                  WHERE user_id = ? AND is_read = 0";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        
        return $result['count'];
    }
    
    // تحديد إشعار كمقروء
    public function markAsRead($notification_id) {
        $query = "UPDATE notifications SET is_read = 1, read_at = NOW() 
                  WHERE id = ?";
        
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$notification_id]);
    }
    
    // تحديد جميع الإشعارات كمقروءة
    public function markAllAsRead($user_id) {
        $query = "UPDATE notifications SET is_read = 1, read_at = NOW() 
                  WHERE user_id = ? AND is_read = 0";
        
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$user_id]);
    }
    
    // حذف إشعار
    public function deleteNotification($notification_id) {
        $query = "DELETE FROM notifications WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$notification_id]);
    }
    
    // إنشاء إشعارات تلقائية للحجوزات القادمة
    public function createUpcomingRentalNotifications() {
        // الحجوزات التي ستنتهي خلال 24 ساعة
        $query = "SELECT r.id, r.customer_id, r.end_date,
                         CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                         CONCAT(car.make, ' ', car.model) as car_name
                  FROM rentals r
                  JOIN customers c ON r.customer_id = c.id
                  JOIN cars car ON r.car_id = car.id
                  WHERE r.status = 'active' 
                    AND r.end_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
                    AND NOT EXISTS (
                        SELECT 1 FROM notifications 
                        WHERE related_id = r.id AND type = 'rental_ending'
                    )";
        
        $stmt = $this->pdo->query($query);
        $rentals = $stmt->fetchAll();
        
        foreach ($rentals as $rental) {
            $title = 'تذكير: انتهاء فترة الإيجار قريباً';
            $message = sprintf(
                'عميلك %s سينتهي إيجار سيارته %s في %s',
                $rental['customer_name'],
                $rental['car_name'],
                date('Y-m-d H:i', strtotime($rental['end_date']))
            );
            
            // إرسال إشعار للأدمن
            $this->createNotification(1, $title, $message, 'rental_ending', $rental['id']);
        }
        
        return count($rentals);
    }
    
    // إنشاء إشعارات للحجوزات المتأخرة
    public function createLateRentalNotifications() {
        $query = "SELECT r.id, r.customer_id, r.end_date, r.late_penalty,
                         CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                         c.phone,
                         CONCAT(car.make, ' ', car.model) as car_name,
                         TIMESTAMPDIFF(HOUR, r.end_date, NOW()) as hours_late
                  FROM rentals r
                  JOIN customers c ON r.customer_id = c.id
                  JOIN cars car ON r.car_id = car.id
                  WHERE r.status = 'active' AND r.end_date < NOW()";
        
        $stmt = $this->pdo->query($query);
        $late_rentals = $stmt->fetchAll();
        
        foreach ($late_rentals as $rental) {
            $title = '⚠️ تأخير في إرجاع السيارة';
            $message = sprintf(
                'العميل %s متأخر في إرجاع %s بـ %d ساعة. غرامة التأخير: %s شيكل',
                $rental['customer_name'],
                $rental['car_name'],
                $rental['hours_late'],
                number_format($rental['late_penalty'], 2)
            );
            
            $this->createNotification(1, $title, $message, 'rental_late', $rental['id']);
        }
        
        return count($late_rentals);
    }
}

$notificationManager = new NotificationManager($pdo);
$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'get';

switch($action) {
    case 'get':
        $unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] == '1';
        $limit = $_GET['limit'] ?? 50;
        $notifications = $notificationManager->getNotifications($user_id, $unread_only, $limit);
        echo json_encode(['success' => true, 'notifications' => $notifications]);
        break;
        
    case 'count':
        $count = $notificationManager->getUnreadCount($user_id);
        echo json_encode(['success' => true, 'count' => $count]);
        break;
        
    case 'mark_read':
        $notification_id = $_POST['notification_id'] ?? null;
        if ($notification_id) {
            $result = $notificationManager->markAsRead($notification_id);
            echo json_encode(['success' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'رقم الإشعار مطلوب']);
        }
        break;
        
    case 'mark_all_read':
        $result = $notificationManager->markAllAsRead($user_id);
        echo json_encode(['success' => $result]);
        break;
        
    case 'delete':
        $notification_id = $_POST['notification_id'] ?? null;
        if ($notification_id) {
            $result = $notificationManager->deleteNotification($notification_id);
            echo json_encode(['success' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'رقم الإشعار مطلوب']);
        }
        break;
        
    case 'create_auto':
        $upcoming = $notificationManager->createUpcomingRentalNotifications();
        $late = $notificationManager->createLateRentalNotifications();
        echo json_encode([
            'success' => true, 
            'upcoming' => $upcoming, 
            'late' => $late,
            'total' => $upcoming + $late
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'إجراء غير صحيح']);
}
?>