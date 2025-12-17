<?php
// api/reports.php
require_once 'common.php';
require_once '../config/database.php';
require_once '../core/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'فشل الاتصال بقاعدة البيانات']);
    exit();
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'financial_summary':
        getFinancialSummary();
        break;
    case 'revenue_chart':
        getRevenueChart();
        break;
    case 'top_cars':
        getTopCars();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action not found']);
}

function getFinancialSummary()
{
    global $db;

    $period = $_GET['period'] ?? 'month';
    $dateFrom = $_GET['from'] ?? date('Y-m-01');
    $dateTo = $_GET['to'] ?? date('Y-m-d');

    try {
        // Revenue
        $stmt = $db->prepare("SELECT COALESCE(SUM(total_amount), 0) as revenue FROM rentals WHERE status IN ('active', 'completed') AND start_date BETWEEN ? AND ?");
        $stmt->execute([$dateFrom, $dateTo]);
        $revenue = $stmt->fetch()['revenue'] ?? 0;

        // Expenses (Maintenance + Insurance)
        $stmt = $db->prepare("SELECT COALESCE(SUM(cost), 0) as expenses FROM maintenance WHERE maintenance_date BETWEEN ? AND ?");
        $stmt->execute([$dateFrom, $dateTo]);
        $expenses = $stmt->fetch()['expenses'] ?? 0;

        // Net Profit
        $profit = $revenue - $expenses;
        $profitMargin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0;

        // Sample data for income sources
        $incomeSources = [
            ['name' => 'إيجار السيارات', 'amount' => $revenue * 0.8, 'count' => 15, 'percentage' => 80],
            ['name' => 'رسوم إضافية', 'amount' => $revenue * 0.15, 'count' => 8, 'percentage' => 15],
            ['name' => 'تأمين', 'amount' => $revenue * 0.05, 'count' => 5, 'percentage' => 5]
        ];

        // Sample data for expense categories
        $expenseCategories = [
            ['name' => 'صيانة السيارات', 'amount' => $expenses * 0.6, 'count' => 10, 'percentage' => 60],
            ['name' => 'وقود', 'amount' => $expenses * 0.25, 'count' => 12, 'percentage' => 25],
            ['name' => 'تأمين', 'amount' => $expenses * 0.15, 'count' => 3, 'percentage' => 15]
        ];

        // Sample transactions
        $transactions = [
            [
                'id' => 1,
                'date' => date('Y-m-d'),
                'type' => 'income',
                'category' => 'إيجار',
                'description' => 'إيجار سيارة تويوتا كامري',
                'amount' => 500,
                'status' => 'completed'
            ],
            [
                'id' => 2,
                'date' => date('Y-m-d', strtotime('-1 day')),
                'type' => 'expense',
                'category' => 'صيانة',
                'description' => 'تغيير زيت',
                'amount' => 150,
                'status' => 'completed'
            ]
        ];

        // Chart data
        $chartData = [
            'income_expense' => [
                'labels' => ['الأسبوع 1', 'الأسبوع 2', 'الأسبوع 3', 'الأسبوع 4'],
                'income' => [5000, 6000, 5500, 7000],
                'expenses' => [2000, 2500, 2200, 2800]
            ],
            'expenses_distribution' => [
                'labels' => ['صيانة', 'وقود', 'تأمين'],
                'values' => [60, 25, 15]
            ],
            'monthly_trend' => [
                'labels' => ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                'values' => [3000, 3500, 3200, 4000, 4500, 5000]
            ]
        ];

        echo json_encode([
            'success' => true,
            'summary' => [
                'total_income' => $revenue,
                'total_expenses' => $expenses,
                'net_profit' => $profit,
                'profit_margin' => $profitMargin,
                'income_change' => 12.5,
                'expenses_change' => 8.3,
                'profit_change' => 15.2
            ],
            'income_sources' => $incomeSources,
            'expense_categories' => $expenseCategories,
            'transactions' => $transactions,
            'chart_data' => $chartData
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'حدث خطأ في تحميل البيانات المالية: ' . $e->getMessage()
        ]);
    }
}

function getRevenueChart()
{
    global $db;

    $year = $_GET['year'] ?? date('Y');

    $stmt = $db->prepare("
        SELECT 
            MONTH(created_at) as month, 
            SUM(total) as revenue 
        FROM bookings 
        WHERE YEAR(created_at) = ? AND status IN ('active', 'completed')
        GROUP BY MONTH(created_at)
        ORDER BY month
    ");
    $stmt->execute([$year]);
    $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $labels = [];
    $data = [];

    for ($i = 1; $i <= 12; $i++) {
        $labels[] = date('F', mktime(0, 0, 0, $i, 1));
        $data[] = $results[$i] ?? 0;
    }

    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'data' => $data
    ]);
}

function getTopCars()
{
    global $db;

    $limit = intval($_GET['limit'] ?? 5);

    $stmt = $db->prepare("
        SELECT 
            c.brand, 
            c.model, 
            COUNT(b.id) as booking_count,
            SUM(b.total) as revenue
        FROM bookings b
        JOIN cars c ON b.car_id = c.id
        WHERE b.status IN ('active', 'completed')
        GROUP BY c.id
        ORDER BY revenue DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    $cars = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'cars' => $cars
    ]);
}
?>