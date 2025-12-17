<?php
// api/bookings.php
require_once 'common.php';
require_once '../config/database.php';
require_once '../core/Auth.php';
require_once '../core/WhatsAppAPI.php';

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

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_bookings':
        getBookings();
        break;
    case 'get_booking_details':
        getBookingDetails();
        break;
    case 'create_booking':
        createBooking();
        break;
    case 'update_booking_status':
        updateBookingStatus();
        break;
    case 'get_stats':
        getBookingStats();
        break;
    case 'check_availability':
        checkAvailability();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action not found']);
}

function getBookings()
{
    global $db;

    // Pagination
    $page = intval($_GET['page'] ?? 1);
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Filters
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    $carId = $_GET['car_id'] ?? '';

    // Build query with JOINs
    $sql = "SELECT b.*, 
                   u.full_name as customer_name, 
                   u.phone as customer_phone,
                   u.email as customer_email,
                   c.brand as car_brand,
                   c.model as car_model,
                   c.year as car_year,
                   c.plate_number as car_plate,
                   c.color as car_color
            FROM bookings b
            JOIN users u ON b.customer_id = u.id
            JOIN cars c ON b.car_id = c.id
            WHERE 1=1";

    $params = [];

    if ($search) {
        $sql .= " AND (b.id LIKE ? OR u.full_name LIKE ? OR u.phone LIKE ? OR c.plate_number LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($status) {
        $sql .= " AND b.status = ?";
        $params[] = $status;
    }

    if ($dateFrom) {
        $sql .= " AND b.start_date >= ?";
        $params[] = $dateFrom;
    }

    if ($dateTo) {
        $sql .= " AND b.end_date <= ?";
        $params[] = $dateTo;
    }

    if ($carId) {
        $sql .= " AND b.car_id = ?";
        $params[] = $carId;
    }

    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM bookings b 
                 JOIN users u ON b.customer_id = u.id
                 JOIN cars c ON b.car_id = c.id
                 WHERE 1=1" . substr($sql, strpos($sql, "WHERE") + 6);

    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];
    $pages = ceil($total / $limit);

    // Add ordering and pagination
    $sql .= " ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'bookings' => $bookings,
        'pagination' => [
            'current_page' => $page,
            'pages' => $pages,
            'total' => $total,
            'limit' => $limit
        ]
    ]);
}

function getBookingDetails()
{
    global $db;

    $id = intval($_GET['id'] ?? 0);

    $sql = "SELECT b.*, 
                   u.full_name as customer_name,
                   u.phone as customer_phone,
                   u.email as customer_email,
                   u.driver_license as customer_license,
                   c.brand as car_brand,
                   c.model as car_model,
                   c.year as car_year,
                   c.plate_number as car_plate,
                   c.color as car_color,
                   c.daily_rate,
                   c.maintenance_cost_per_day,
                   c.insurance_type
            FROM bookings b
            JOIN users u ON b.customer_id = u.id
            JOIN cars c ON b.car_id = c.id
            WHERE b.id = ?";

    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);
    $booking = $stmt->fetch();

    if ($booking) {
        // Calculate remaining amount
        $paymentStmt = $db->prepare("SELECT SUM(amount) as total_paid FROM payments WHERE booking_id = ?");
        $paymentStmt->execute([$id]);
        $payments = $paymentStmt->fetch();

        $booking['total_paid'] = $payments['total_paid'] ?? 0;
        $booking['remaining'] = $booking['total'] - $booking['total_paid'];

        echo json_encode([
            'success' => true,
            'booking' => $booking
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'الحجز غير موجود'
        ]);
    }
}

function createBooking()
{
    global $db;

    $customer_id = $_SESSION['user_id'];
    $car_id = intval($_POST['car_id']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $insurance_type = $_POST['insurance_type'] ?? 'third_party';

    // Check car availability
    $availability = checkCarAvailability($car_id, $start_date, $end_date);
    if (!$availability['available']) {
        echo json_encode([
            'success' => false,
            'message' => 'السيارة غير متاحة في هذه الفترة'
        ]);
        return;
    }

    // Get car details
    $carStmt = $db->prepare("SELECT * FROM cars WHERE id = ?");
    $carStmt->execute([$car_id]);
    $car = $carStmt->fetch();

    if (!$car) {
        echo json_encode([
            'success' => false,
            'message' => 'السيارة غير موجودة'
        ]);
        return;
    }

    // Calculate days
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $days = $start->diff($end)->days + 1;

    // Calculate costs
    $daily_rate = $car['daily_rate'];
    $maintenance_cost_per_day = $car['maintenance_cost_per_day'] ?? 15;
    $insurance_daily_cost = calculateInsuranceCost($car['insurance_type'], $insurance_type, $daily_rate);

    $subtotal = $daily_rate * $days;
    $maintenance_cost = $maintenance_cost_per_day * $days;
    $insurance_cost = $insurance_daily_cost * $days;

    $total_before_tax = $subtotal + $maintenance_cost + $insurance_cost;
    $tax_rate = 17; // 17% VAT
    $tax = $total_before_tax * ($tax_rate / 100);

    $total = $total_before_tax + $tax;
    $deposit = $total * 0.4; // 40% deposit

    // Insert booking
    $sql = "INSERT INTO bookings (customer_id, car_id, start_date, end_date, days, 
                                  daily_rate, subtotal, maintenance_cost, insurance_type, 
                                  insurance_cost, tax_rate, tax, total, deposit, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

    $stmt = $db->prepare($sql);
    $success = $stmt->execute([
        $customer_id,
        $car_id,
        $start_date,
        $end_date,
        $days,
        $daily_rate,
        $subtotal,
        $maintenance_cost,
        $insurance_type,
        $insurance_cost,
        $tax_rate,
        $tax,
        $total,
        $deposit
    ]);

    if ($success) {
        $booking_id = $db->lastInsertId();

        // Update car status
        $updateStmt = $db->prepare("UPDATE cars SET status = 'reserved' WHERE id = ?");
        $updateStmt->execute([$car_id]);

        // Send WhatsApp confirmation
        $whatsapp = new WhatsAppAPI();
        $customerStmt = $db->prepare("SELECT phone FROM users WHERE id = ?");
        $customerStmt->execute([$customer_id]);
        $customer = $customerStmt->fetch();

        if ($customer && $customer['phone']) {
            $bookingDetails = [
                'booking_id' => $booking_id,
                'car_name' => $car['brand'] . ' ' . $car['model'],
                'start_date' => $start_date,
                'end_date' => $end_date,
                'total' => $total
            ];

            $whatsapp->sendBookingConfirmation($customer['phone'], $bookingDetails);
        }

        echo json_encode([
            'success' => true,
            'message' => 'تم إنشاء الحجز بنجاح',
            'booking_id' => $booking_id,
            'total' => $total,
            'deposit' => $deposit
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'فشل إنشاء الحجز'
        ]);
    }
}

function updateBookingStatus()
{
    global $db;

    $booking_id = intval($_POST['booking_id']);
    $status = $_POST['status'];
    $notes = $_POST['notes'] ?? '';

    // Get current booking
    $stmt = $db->prepare("SELECT car_id, status FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        echo json_encode([
            'success' => false,
            'message' => 'الحجز غير موجود'
        ]);
        return;
    }

    // Update booking status
    $updateStmt = $db->prepare("UPDATE bookings SET status = ?, updated_at = NOW() WHERE id = ?");
    $success = $updateStmt->execute([$status, $booking_id]);

    if ($success) {
        // Update car status based on booking status
        $car_status = 'available';
        if ($status === 'active') {
            $car_status = 'rented';
        } elseif ($status === 'pending' || $status === 'reserved') {
            $car_status = 'reserved';
        }

        $carStmt = $db->prepare("UPDATE cars SET status = ? WHERE id = ?");
        $carStmt->execute([$car_status, $booking['car_id']]);

        // Log status change
        $logStmt = $db->prepare("INSERT INTO booking_logs (booking_id, old_status, new_status, notes, created_by, created_at) 
                                VALUES (?, ?, ?, ?, ?, NOW())");
        $logStmt->execute([$booking_id, $booking['status'], $status, $notes, $_SESSION['user_id']]);

        // Send notification if status changed to active or completed
        if ($status === 'active' || $status === 'completed') {
            $whatsapp = new WhatsAppAPI();
            $bookingStmt = $db->prepare("
                SELECT u.phone, c.brand, c.model 
                FROM bookings b
                JOIN users u ON b.customer_id = u.id
                JOIN cars c ON b.car_id = c.id
                WHERE b.id = ?
            ");
            $bookingStmt->execute([$booking_id]);
            $bookingDetails = $bookingStmt->fetch();

            if ($bookingDetails && $bookingDetails['phone']) {
                if ($status === 'active') {
                    $whatsapp->sendBookingActivation($bookingDetails['phone'], $booking_id);
                } elseif ($status === 'completed') {
                    $whatsapp->sendBookingCompletion($bookingDetails['phone'], $booking_id);
                }
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'تم تحديث حالة الحجز بنجاح'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'فشل تحديث حالة الحجز'
        ]);
    }
}

function getBookingStats()
{
    global $db;

    $stats = [];

    // Count by status
    $stmt = $db->query("SELECT status, COUNT(*) as count FROM bookings GROUP BY status");
    $statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $stats['pending'] = $statusCounts['pending'] ?? 0;
    $stats['active'] = $statusCounts['active'] ?? 0;
    $stats['completed'] = $statusCounts['completed'] ?? 0;
    $stats['cancelled'] = $statusCounts['cancelled'] ?? 0;

    // Today's bookings
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM bookings WHERE DATE(created_at) = ?");
    $stmt->execute([$today]);
    $stats['today'] = $stmt->fetch()['count'];

    // Monthly revenue
    $currentMonth = date('Y-m');
    $stmt = $db->prepare("SELECT SUM(total) as revenue FROM bookings WHERE DATE_FORMAT(created_at, '%Y-%m') = ? AND status IN ('active', 'completed')");
    $stmt->execute([$currentMonth]);
    $stats['monthly_revenue'] = $stmt->fetch()['revenue'] ?? 0;

    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
}

function checkAvailability()
{
    global $db;

    $car_id = intval($_GET['car_id']);
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];

    $availability = checkCarAvailability($car_id, $start_date, $end_date);

    echo json_encode([
        'success' => true,
        'available' => $availability['available'],
        'message' => $availability['message'],
        'conflicts' => $availability['conflicts']
    ]);
}

function checkCarAvailability($car_id, $start_date, $end_date)
{
    global $db;

    $sql = "SELECT * FROM bookings 
            WHERE car_id = ? 
            AND status NOT IN ('cancelled', 'completed')
            AND (
                (start_date BETWEEN ? AND ?)
                OR (end_date BETWEEN ? AND ?)
                OR (? BETWEEN start_date AND end_date)
                OR (? BETWEEN start_date AND end_date)
            )";

    $stmt = $db->prepare($sql);
    $stmt->execute([$car_id, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date]);
    $conflicts = $stmt->fetchAll();

    if (count($conflicts) > 0) {
        $conflict_dates = array_map(function ($conflict) {
            return $conflict['start_date'] . ' إلى ' . $conflict['end_date'];
        }, $conflicts);

        return [
            'available' => false,
            'message' => 'السيارة محجوزة في الفترات التالية: ' . implode(', ', $conflict_dates),
            'conflicts' => $conflicts
        ];
    }

    return [
        'available' => true,
        'message' => 'السيارة متاحة',
        'conflicts' => []
    ];
}

function calculateInsuranceCost($car_insurance_type, $selected_insurance_type, $daily_rate)
{
    $rates = [
        'third_party' => 0.08,  // 8%
        'partial' => 0.12,      // 12%
        'full' => 0.15          // 15%
    ];

    $rate = $rates[$selected_insurance_type] ?? 0.08;
    return $daily_rate * $rate;
}
?>