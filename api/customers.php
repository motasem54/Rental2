<?php
// api/customers.php
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
    case 'get_customers':
        getCustomers();
        break;
    case 'get_customer':
        getCustomer();
        break;
    case 'add_customer':
        addCustomer();
        break;
    case 'update_customer':
        updateCustomer();
        break;
    case 'delete_customer':
        deleteCustomer();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action not found']);
}

function getCustomers()
{
    global $db;

    $page = intval($_GET['page'] ?? 1);
    $limit = 10;
    $offset = ($page - 1) * $limit;
    $search = $_GET['search'] ?? '';

    $sql = "SELECT * FROM users WHERE role = 'customer'";
    $params = [];

    if ($search) {
        $sql .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR national_id LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_fill(0, 4, $searchTerm);
    }

    // Count total
    $countSql = str_replace('*', 'COUNT(*) as total', $sql);
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];
    $pages = ceil($total / $limit);

    // Get data
    $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'customers' => $customers,
        'pagination' => [
            'current_page' => $page,
            'pages' => $pages,
            'total' => $total,
            'limit' => $limit
        ]
    ]);
}

function getCustomer()
{
    global $db;
    $id = intval($_GET['id'] ?? 0);

    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'customer'");
    $stmt->execute([$id]);
    $customer = $stmt->fetch();

    echo json_encode([
        'success' => !!$customer,
        'customer' => $customer
    ]);
}

function addCustomer()
{
    global $db;

    $data = [
        'username' => $_POST['username'],
        'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        'email' => $_POST['email'],
        'full_name' => $_POST['full_name'],
        'phone' => $_POST['phone'],
        'national_id' => $_POST['national_id'] ?? '',
        'driver_license' => $_POST['driver_license'] ?? '',
        'address' => $_POST['address'] ?? '',
        'role' => 'customer',
        'created_at' => date('Y-m-d H:i:s')
    ];

    // Check if username or email exists
    $checkStmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $checkStmt->execute([$data['username'], $data['email']]);
    if ($checkStmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'اسم المستخدم أو البريد الإلكتروني موجود مسبقاً']);
        return;
    }

    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));

    $sql = "INSERT INTO users ($columns) VALUES ($placeholders)";
    $stmt = $db->prepare($sql);
    $success = $stmt->execute($data);

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'تم إضافة العميل بنجاح' : 'فشل إضافة العميل'
    ]);
}

function updateCustomer()
{
    global $db;

    $id = intval($_POST['id']);
    $data = [
        'email' => $_POST['email'],
        'full_name' => $_POST['full_name'],
        'phone' => $_POST['phone'],
        'national_id' => $_POST['national_id'] ?? '',
        'driver_license' => $_POST['driver_license'] ?? '',
        'address' => $_POST['address'] ?? '',
        'updated_at' => date('Y-m-d H:i:s')
    ];

    if (!empty($_POST['password'])) {
        $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    $setClause = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($data)));
    $data['id'] = $id;

    $sql = "UPDATE users SET $setClause WHERE id = :id AND role = 'customer'";
    $stmt = $db->prepare($sql);
    $success = $stmt->execute($data);

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'تم تحديث بيانات العميل بنجاح' : 'فشل تحديث البيانات'
    ]);
}

function deleteCustomer()
{
    global $db;
    $id = intval($_POST['id']);

    // Check if customer has bookings
    $checkStmt = $db->prepare("SELECT id FROM bookings WHERE customer_id = ?");
    $checkStmt->execute([$id]);
    if ($checkStmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'لا يمكن حذف العميل لوجود حجوزات مرتبطة به']);
        return;
    }

    $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = 'customer'");
    $success = $stmt->execute([$id]);

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'تم حذف العميل بنجاح' : 'فشل حذف العميل'
    ]);
}
?>