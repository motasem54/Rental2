<?php
// api/calculate-penalties.php
// نظام حساب الغرامات التلقائي
session_start();
require_once '../config/database.php';
require_once '../core/Auth.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit;
}

class PenaltyCalculator {
    private $pdo;
    private $hourly_penalty = 50; // 50 شيكل لكل ساعة تأخير
    private $daily_penalty = 500; // 500 شيكل لكل يوم تأخير
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // حساب غرامة التأخير
    public function calculateLatePenalty($rental_id) {
        $query = "SELECT r.*, 
                         TIMESTAMPDIFF(HOUR, r.end_date, NOW()) as hours_late,
                         TIMESTAMPDIFF(DAY, r.end_date, NOW()) as days_late
                  FROM rentals r
                  WHERE r.id = ? AND r.status = 'active' AND r.end_date < NOW()";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$rental_id]);
        $rental = $stmt->fetch();
        
        if (!$rental) {
            return ['penalty' => 0, 'late_hours' => 0, 'late_days' => 0];
        }
        
        $hours_late = $rental['hours_late'];
        $days_late = $rental['days_late'];
        
        // حساب الغرامة
        $penalty = 0;
        
        if ($days_late > 0) {
            // غرامة يومية إذا تأخر أكثر من يوم
            $penalty = $days_late * $this->daily_penalty;
        } else if ($hours_late > 0) {
            // غرامة بالساعة إذا تأخر أقل من يوم
            $penalty = $hours_late * $this->hourly_penalty;
        }
        
        // تحديث الغرامة في قاعدة البيانات
        $this->updatePenalty($rental_id, $penalty);
        
        return [
            'penalty' => $penalty,
            'late_hours' => $hours_late,
            'late_days' => $days_late,
            'rental_id' => $rental_id
        ];
    }
    
    // تحديث الغرامة
    private function updatePenalty($rental_id, $penalty) {
        $query = "UPDATE rentals SET late_penalty = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$penalty, $rental_id]);
    }
    
    // حساب غرامات جميع الحجوزات المتأخرة
    public function calculateAllLatePenalties() {
        $query = "SELECT id FROM rentals 
                  WHERE status = 'active' AND end_date < NOW()";
        
        $stmt = $this->pdo->query($query);
        $rentals = $stmt->fetchAll();
        
        $results = [];
        foreach ($rentals as $rental) {
            $results[] = $this->calculateLatePenalty($rental['id']);
        }
        
        return $results;
    }
    
    // الحصول على الحجوزات المتأخرة
    public function getLateRentals() {
        $query = "SELECT r.id, r.end_date,
                         CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                         c.phone,
                         CONCAT(car.make, ' ', car.model) as car_name,
                         TIMESTAMPDIFF(HOUR, r.end_date, NOW()) as hours_late,
                         TIMESTAMPDIFF(DAY, r.end_date, NOW()) as days_late,
                         r.late_penalty
                  FROM rentals r
                  JOIN customers c ON r.customer_id = c.id
                  JOIN cars car ON r.car_id = car.id
                  WHERE r.status = 'active' AND r.end_date < NOW()
                  ORDER BY r.end_date ASC";
        
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$calculator = new PenaltyCalculator($pdo);

$action = $_GET['action'] ?? 'calculate';

switch($action) {
    case 'calculate':
        $rental_id = $_GET['rental_id'] ?? null;
        if ($rental_id) {
            $result = $calculator->calculateLatePenalty($rental_id);
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'رقم الحجز مطلوب']);
        }
        break;
        
    case 'calculate_all':
        $results = $calculator->calculateAllLatePenalties();
        echo json_encode(['success' => true, 'data' => $results, 'count' => count($results)]);
        break;
        
    case 'get_late':
        $late = $calculator->getLateRentals();
        echo json_encode(['success' => true, 'data' => $late]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'إجراء غير صحيح']);
}
?>