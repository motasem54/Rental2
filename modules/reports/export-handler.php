<?php
// modules/reports/export-handler.php
// نظام التصدير الشامل
session_start();
require_once '../../config/database.php';
require_once '../../core/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    die(json_encode(['success' => false, 'message' => 'غير مسموح']));
}

$export_type = $_GET['type'] ?? 'csv';
$report_type = $_GET['report'] ?? 'rentals';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

class ExportHandler {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Export to CSV
    public function exportCSV($data, $filename, $headers) {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Add BOM for UTF-8
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        
        // Write headers
        fputcsv($output, $headers);
        
        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    // Export to Excel (HTML table format)
    public function exportExcel($data, $filename, $headers) {
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo "\xEF\xBB\xBF";
        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        echo '<head><meta charset="UTF-8"></head>';
        echo '<body><table border="1">';
        
        // Headers
        echo '<tr style="background-color: #FF8C42; color: white; font-weight: bold;">';
        foreach ($headers as $header) {
            echo '<td>' . htmlspecialchars($header) . '</td>';
        }
        echo '</tr>';
        
        // Data
        foreach ($data as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . htmlspecialchars($cell) . '</td>';
            }
            echo '</tr>';
        }
        
        echo '</table></body></html>';
        exit;
    }
    
    // Export to JSON
    public function exportJSON($data, $filename) {
        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.json"');
        
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Get Rentals Data
    public function getRentalsData($start_date, $end_date) {
        $query = "SELECT 
                    r.id,
                    CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                    c.phone,
                    CONCAT(car.make, ' ', car.model, ' ', car.year) as car,
                    car.plate_number,
                    r.start_date,
                    r.end_date,
                    DATEDIFF(r.end_date, r.start_date) as days,
                    r.total_amount,
                    r.status,
                    u.full_name as created_by
                  FROM rentals r
                  JOIN customers c ON r.customer_id = c.id
                  JOIN cars car ON r.car_id = car.id
                  LEFT JOIN users u ON r.created_by = u.id
                  WHERE r.start_date >= ? AND r.end_date <= ?
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$start_date, $end_date]);
        
        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = [
                $row['id'],
                $row['customer_name'],
                $row['phone'],
                $row['car'],
                $row['plate_number'],
                $row['start_date'],
                $row['end_date'],
                $row['days'],
                $row['total_amount'] . ' ₪',
                $row['status'],
                $row['created_by']
            ];
        }
        
        return $data;
    }
    
    // Get Cars Data
    public function getCarsData() {
        $query = "SELECT id, make, model, year, color, plate_number, 
                         daily_rate, status, mileage
                  FROM cars
                  ORDER BY id DESC";
        
        $stmt = $this->pdo->query($query);
        
        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = [
                $row['id'],
                $row['make'],
                $row['model'],
                $row['year'],
                $row['color'],
                $row['plate_number'],
                $row['daily_rate'] . ' ₪',
                $row['status'],
                $row['mileage'] . ' km'
            ];
        }
        
        return $data;
    }
    
    // Get Financial Report
    public function getFinancialData($start_date, $end_date) {
        $query = "SELECT 
                    DATE(r.created_at) as date,
                    COUNT(r.id) as total_rentals,
                    SUM(r.total_amount) as total_revenue,
                    SUM(CASE WHEN r.status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN r.status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN r.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                  FROM rentals r
                  WHERE r.created_at >= ? AND r.created_at <= ?
                  GROUP BY DATE(r.created_at)
                  ORDER BY date DESC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$start_date, $end_date]);
        
        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = [
                $row['date'],
                $row['total_rentals'],
                $row['total_revenue'] . ' ₪',
                $row['active'],
                $row['completed'],
                $row['cancelled']
            ];
        }
        
        return $data;
    }
}

// Initialize Export Handler
$exporter = new ExportHandler($pdo);

// Determine report type and get data
switch ($report_type) {
    case 'rentals':
        $data = $exporter->getRentalsData($start_date, $end_date);
        $headers = ['رقم الحجز', 'العميل', 'الهاتف', 'السيارة', 'رقم اللوحة', 'تاريخ البدء', 'تاريخ الانتهاء', 'عدد الأيام', 'المبلغ', 'الحالة', 'بواسطة'];
        $filename = 'rentals_report_' . date('Y-m-d');
        break;
        
    case 'cars':
        $data = $exporter->getCarsData();
        $headers = ['الرقم', 'الماركة', 'الموديل', 'السنة', 'اللون', 'رقم اللوحة', 'السعر اليومي', 'الحالة', 'عداد السرعة'];
        $filename = 'cars_report_' . date('Y-m-d');
        break;
        
    case 'financial':
        $data = $exporter->getFinancialData($start_date, $end_date);
        $headers = ['التاريخ', 'إجمالي الحجوزات', 'إجمالي الإيرادات', 'نشطة', 'مكتملة', 'ملغاة'];
        $filename = 'financial_report_' . date('Y-m-d');
        break;
        
    default:
        die(json_encode(['success' => false, 'message' => 'نوع التقرير غير صحيح']));
}

// Export based on type
switch ($export_type) {
    case 'csv':
        $exporter->exportCSV($data, $filename, $headers);
        break;
        
    case 'excel':
        $exporter->exportExcel($data, $filename, $headers);
        break;
        
    case 'json':
        $exporter->exportJSON($data, $filename);
        break;
        
    default:
        die(json_encode(['success' => false, 'message' => 'نوع التصدير غير صحيح']));
}
?>