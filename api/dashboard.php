<?php
// api/dashboard.php
// API للوحة التحكم
session_start();
require_once '../config/database.php';
require_once '../core/Auth.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit;
}

class DashboardAPI {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // إحصائيات لوحة التحكم
    public function getStats() {
        // إجمالي السيارات
        $total_cars = $this->pdo->query("SELECT COUNT(*) as count FROM cars")->fetch()['count'];
        
        // السيارات المتاحة
        $available_cars = $this->pdo->query(
            "SELECT COUNT(*) as count FROM cars WHERE status = 'available'"
        )->fetch()['count'];
        
        // الحجوزات النشطة
        $active_rentals = $this->pdo->query(
            "SELECT COUNT(*) as count FROM rentals WHERE status = 'active'"
        )->fetch()['count'];
        
        // الدخل الشهري
        $monthly_income = $this->pdo->query(
            "SELECT COALESCE(SUM(total_amount), 0) as income 
             FROM rentals 
             WHERE MONTH(created_at) = MONTH(NOW()) 
               AND YEAR(created_at) = YEAR(NOW())
               AND status != 'cancelled'"
        )->fetch()['income'];
        
        // الدخل الشهر الماضي
        $last_month_income = $this->pdo->query(
            "SELECT COALESCE(SUM(total_amount), 0) as income 
             FROM rentals 
             WHERE MONTH(created_at) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) 
               AND YEAR(created_at) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))
               AND status != 'cancelled'"
        )->fetch()['income'];
        
        // نسبة التغيير في الدخل
        $income_change = 0;
        if ($last_month_income > 0) {
            $income_change = (($monthly_income - $last_month_income) / $last_month_income) * 100;
        }
        
        // السيارات التي تحتاج صيانة
        $maintenance_due = $this->pdo->query(
            "SELECT COUNT(*) as count FROM cars 
             WHERE next_maintenance_date <= DATE_ADD(NOW(), INTERVAL 7 DAY)
               OR mileage >= (last_maintenance_mileage + 5000)"
        )->fetch()['count'];
        
        // حجوزات اليوم
        $today_rentals = $this->pdo->query(
            "SELECT COUNT(*) as count FROM rentals 
             WHERE DATE(created_at) = CURDATE()"
        )->fetch()['count'];
        
        // سيارات مضافة اليوم
        $cars_today = $this->pdo->query(
            "SELECT COUNT(*) as count FROM cars 
             WHERE DATE(created_at) = CURDATE()"
        )->fetch()['count'];
        
        // الحجوزات المعلقة
        $pending_bookings = $this->pdo->query(
            "SELECT COUNT(*) as count FROM rentals 
             WHERE status = 'pending'"
        )->fetch()['count'];
        
        // حجم قاعدة البيانات
        $db_size = $this->pdo->query(
            "SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
             FROM information_schema.tables 
             WHERE table_schema = DATABASE()"
        )->fetch()['size_mb'];
        
        return [
            'total_cars' => $total_cars,
            'available_cars' => $available_cars,
            'active_rentals' => $active_rentals,
            'monthly_income' => number_format($monthly_income, 2),
            'income_change' => round($income_change, 1),
            'maintenance_due' => $maintenance_due,
            'rentals_today' => $today_rentals,
            'cars_today' => $cars_today,
            'pending_bookings' => $pending_bookings,
            'db_size' => $db_size,
            'uptime' => $this->getUptime(),
            'performance' => $this->getPerformance(),
            'updates_count' => 0
        ];
    }
    
    // بيانات الرسوم البيانية
    public function getChartData() {
        // بيانات الإيرادات لآخر 6 أشهر
        $revenue_query = "SELECT 
                            DATE_FORMAT(created_at, '%Y-%m') as month,
                            SUM(total_amount) as revenue
                          FROM rentals
                          WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                            AND status != 'cancelled'
                          GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                          ORDER BY month ASC";
        
        $revenue_stmt = $this->pdo->query($revenue_query);
        $revenue_data = [];
        while ($row = $revenue_stmt->fetch()) {
            $revenue_data[] = $row['revenue'];
        }
        
        // توزيع الحجوزات حسب الحالة
        $distribution_query = "SELECT 
                                 status,
                                 COUNT(*) as count
                               FROM rentals
                               GROUP BY status";
        
        $distribution_stmt = $this->pdo->query($distribution_query);
        $distribution_data = [];
        while ($row = $distribution_stmt->fetch()) {
            $distribution_data[] = $row['count'];
        }
        
        return [
            'revenue' => $revenue_data,
            'distribution' => $distribution_data
        ];
    }
    
    // وقت التشغيل
    private function getUptime() {
        $uptime_query = "SELECT TIMESTAMPDIFF(DAY, MIN(created_at), NOW()) as days 
                         FROM rentals";
        $result = $this->pdo->query($uptime_query)->fetch();
        return $result['days'] ?? 0;
    }
    
    // أداء النظام
    private function getPerformance() {
        // حساب بسيط للأداء بناءً على عدد الحجوزات النشطة
        $active = $this->pdo->query(
            "SELECT COUNT(*) as count FROM rentals WHERE status = 'active'"
        )->fetch()['count'];
        
        $total = $this->pdo->query(
            "SELECT COUNT(*) as count FROM rentals"
        )->fetch()['count'];
        
        if ($total == 0) return 85;
        
        $performance = 100 - (($active / $total) * 50);
        return round(max(60, min(100, $performance)));
    }
}

$api = new DashboardAPI($pdo);
$action = $_GET['action'] ?? 'get_stats';

switch($action) {
    case 'get_stats':
        $stats = $api->getStats();
        $charts = $api->getChartData();
        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'charts' => $charts
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'إجراء غير صحيح']);
}
?>