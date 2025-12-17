<?php
// api/cars.php
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

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_cars':
        getCars();
        break;
    case 'get_car':
        getCar();
        break;
    case 'get_car_details':
        getCarDetails();
        break;
    case 'add_car':
        addCar();
        break;
    case 'update_car':
        updateCar();
        break;
    case 'delete_car':
        deleteCar();
        break;
    case 'get_stats':
        getStats();
        break;
    case 'get_brands':
        getBrands();
        break;
    case 'export':
        exportCars();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action not found']);
}

function getCars()
{
    global $db;

    // Pagination
    $page = intval($_GET['page'] ?? 1);
    $limit = 12;
    $offset = ($page - 1) * $limit;

    // Filters
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $brand = $_GET['brand'] ?? '';
    $yearFrom = $_GET['year_from'] ?? '';
    $yearTo = $_GET['year_to'] ?? '';

    // Build query
    $sql = "SELECT * FROM cars WHERE 1=1";
    $params = [];

    if ($search) {
        $sql .= " AND (brand LIKE ? OR model LIKE ? OR plate_number LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }

    if ($brand) {
        $sql .= " AND brand = ?";
        $params[] = $brand;
    }

    if ($yearFrom) {
        $sql .= " AND year >= ?";
        $params[] = $yearFrom;
    }

    if ($yearTo) {
        $sql .= " AND year <= ?";
        $params[] = $yearTo;
    }

    // Get total count
    $countStmt = $db->prepare(str_replace('*', 'COUNT(*) as total', $sql));
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];
    $pages = ceil($total / $limit);

    // Add ordering and pagination
    $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $cars = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'cars' => $cars,
        'pagination' => [
            'current_page' => $page,
            'pages' => $pages,
            'total' => $total,
            'limit' => $limit
        ]
    ]);
}

function getCar()
{
    global $db;

    $id = intval($_GET['id'] ?? 0);
    $stmt = $db->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->execute([$id]);
    $car = $stmt->fetch();

    echo json_encode([
        'success' => !!$car,
        'car' => $car
    ]);
}

function getCarDetails()
{
    global $db;

    $id = intval($_GET['id'] ?? 0);
    $stmt = $db->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->execute([$id]);
    $car = $stmt->fetch();

    // Get booking stats
    $statsStmt = $db->prepare("
        SELECT 
            COUNT(*) as total_bookings,
            SUM(days) as total_days,
            SUM(total) as total_income
        FROM bookings 
        WHERE car_id = ? AND status IN ('completed', 'active')
    ");
    $statsStmt->execute([$id]);
    $stats = $statsStmt->fetch();

    echo json_encode([
        'success' => !!$car,
        'car' => $car,
        'stats' => $stats
    ]);
}

function addCar()
{
    global $db;

    $data = [
        'brand' => $_POST['brand'],
        'model' => $_POST['model'],
        'year' => $_POST['year'],
        'plate_number' => $_POST['plate_number'],
        'color' => $_POST['color'] ?? '',
        'daily_rate' => $_POST['daily_rate'],
        'maintenance_cost_per_day' => $_POST['maintenance_cost_per_day'] ?? 15,
        'insurance_type' => $_POST['insurance_type'] ?? 'third_party',
        'features' => $_POST['features'] ?? '',
        'status' => 'available',
        'created_at' => date('Y-m-d H:i:s')
    ];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/cars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $data['image'] = 'cars/' . $fileName;
        }
    }

    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));

    $sql = "INSERT INTO cars ($columns) VALUES ($placeholders)";
    $stmt = $db->prepare($sql);

    $success = $stmt->execute($data);

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'تمت إضافة السيارة بنجاح' : 'فشل إضافة السيارة',
        'id' => $success ? $db->lastInsertId() : null
    ]);
}

function updateCar()
{
    global $db;

    $id = intval($_POST['id'] ?? 0);
    $data = [
        'brand' => $_POST['brand'],
        'model' => $_POST['model'],
        'year' => $_POST['year'],
        'plate_number' => $_POST['plate_number'],
        'color' => $_POST['color'] ?? '',
        'daily_rate' => $_POST['daily_rate'],
        'maintenance_cost_per_day' => $_POST['maintenance_cost_per_day'] ?? 15,
        'status' => $_POST['status'] ?? 'available',
        'features' => $_POST['features'] ?? '',
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $setClause = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($data)));
    $data['id'] = $id;

    $sql = "UPDATE cars SET $setClause WHERE id = :id";
    $stmt = $db->prepare($sql);
    $success = $stmt->execute($data);

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'تم تحديث السيارة بنجاح' : 'فشل تحديث السيارة'
    ]);
}

function deleteCar()
{
    global $db;

    $id = intval($_POST['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM cars WHERE id = ?");
    $success = $stmt->execute([$id]);

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'تم حذف السيارة بنجاح' : 'فشل حذف السيارة'
    ]);
}

function getStats()
{
    global $db;

    $stats = [];

    // Total cars
    $stmt = $db->query("SELECT COUNT(*) as total FROM cars");
    $stats['total'] = $stmt->fetch()['total'];

    // Available cars
    $stmt = $db->query("SELECT COUNT(*) as available FROM cars WHERE status = 'available'");
    $stats['available'] = $stmt->fetch()['available'];

    // Rented cars
    $stmt = $db->query("SELECT COUNT(*) as rented FROM cars WHERE status = 'rented'");
    $stats['rented'] = $stmt->fetch()['rented'];

    // Maintenance cars
    $stmt = $db->query("SELECT COUNT(*) as maintenance FROM cars WHERE status = 'maintenance'");
    $stats['maintenance'] = $stmt->fetch()['maintenance'];

    // Total value
    $stmt = $db->query("SELECT SUM(daily_rate * 365) as total_value FROM cars");
    $stats['total_value'] = number_format($stmt->fetch()['total_value'] ?? 0, 2);

    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
}

function getBrands()
{
    global $db;

    $stmt = $db->query("SELECT DISTINCT brand FROM cars ORDER BY brand");
    $brands = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'brands' => $brands
    ]);
}

function exportCars()
{
    global $db;

    $format = $_GET['format'] ?? 'excel';
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $brand = $_GET['brand'] ?? '';

    // Build query
    $sql = "SELECT * FROM cars WHERE 1=1";
    $params = [];

    if ($search) {
        $sql .= " AND (brand LIKE ? OR model LIKE ? OR plate_number LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }

    if ($brand) {
        $sql .= " AND brand = ?";
        $params[] = $brand;
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $cars = $stmt->fetchAll();

    if ($format === 'excel') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="cars_export.xls"');

        echo "<table border='1'>";
        echo "<tr>
                <th>الماركة</th>
                <th>الموديل</th>
                <th>سنة الصنع</th>
                <th>رقم اللوحة</th>
                <th>السعر اليومي</th>
                <th>نوع التأمين</th>
                <th>الحالة</th>
                <th>تاريخ الإضافة</th>
              </tr>";

        foreach ($cars as $car) {
            echo "<tr>
                    <td>{$car['brand']}</td>
                    <td>{$car['model']}</td>
                    <td>{$car['year']}</td>
                    <td>{$car['plate_number']}</td>
                    <td>{$car['daily_rate']} ₪</td>
                    <td>{$car['insurance_type']}</td>
                    <td>{$car['status']}</td>
                    <td>{$car['created_at']}</td>
                  </tr>";
        }

        echo "</table>";
    } elseif ($format === 'pdf') {
        // PDF generation would require a library like TCPDF or Dompdf
        // This is a simplified version
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="cars_export.pdf"');

        $html = "<h2>قائمة السيارات</h2>
                <table border='1' style='width:100%; border-collapse:collapse;'>
                <tr>
                    <th>الماركة</th>
                    <th>الموديل</th>
                    <th>رقم اللوحة</th>
                    <th>السعر</th>
                    <th>الحالة</th>
                </tr>";

        foreach ($cars as $car) {
            $html .= "<tr>
                        <td>{$car['brand']}</td>
                        <td>{$car['model']}</td>
                        <td>{$car['plate_number']}</td>
                        <td>{$car['daily_rate']} ₪</td>
                        <td>{$car['status']}</td>
                      </tr>";
        }

        $html .= "</table>";

        // In real implementation, use a PDF library
        echo $html;
    } elseif ($format === 'print') {
        echo "<table border='1' style='width:100%; border-collapse:collapse; text-align:center;'>
                <tr>
                    <th>الماركة</th>
                    <th>الموديل</th>
                    <th>رقم اللوحة</th>
                    <th>السعر اليومي</th>
                    <th>الحالة</th>
                </tr>";

        foreach ($cars as $car) {
            $statusText = '';
            switch ($car['status']) {
                case 'available':
                    $statusText = 'متاحة';
                    break;
                case 'rented':
                    $statusText = 'مؤجرة';
                    break;
                case 'maintenance':
                    $statusText = 'صيانة';
                    break;
                case 'reserved':
                    $statusText = 'محجوزة';
                    break;
            }

            echo "<tr>
                    <td>{$car['brand']}</td>
                    <td>{$car['model']}</td>
                    <td>{$car['plate_number']}</td>
                    <td>{$car['daily_rate']} ₪</td>
                    <td>{$statusText}</td>
                  </tr>";
        }

        echo "</table>";
    }
}
?>