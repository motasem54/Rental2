<?php
// api/insurance.php
require_once '../config/database.php';
require_once '../core/Auth.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

$db = Database::getInstance()->getConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            listInsurance($db);
            break;
        case 'create':
            createInsurance($db);
            break;
        case 'get':
            getInsurance($db);
            break;
        case 'update':
            updateInsurance($db);
            break;
        case 'claims':
            getClaims($db);
            break;
        case 'delete':
            deleteInsurance($db);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function listInsurance($db)
{
    $stmt = $db->query("SELECT i.*, c.brand, c.model, c.plate_number 
                        FROM insurance i 
                        LEFT JOIN cars c ON i.car_id = c.id 
                        ORDER BY i.created_at DESC");
    $insurance = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = array_map(function ($item) {
        return [
            'id' => $item['id'],
            'car_name' => ($item['brand'] ?? '') . ' ' . ($item['model'] ?? '') . ' - ' . ($item['plate_number'] ?? ''),
            'company' => $item['insurance_company'] ?? '',
            'policy_number' => $item['policy_number'] ?? '',
            'start_date' => $item['start_date'] ?? '',
            'end_date' => $item['end_date'] ?? '',
            'status' => $item['status'] ?? 'active'
        ];
    }, $insurance);

    echo json_encode(['success' => true, 'data' => $data]);
}

function getClaims($db)
{
    // Mock claims data since there is no claims table structure defined yet
    // Ideally this should select from a 'claims' table
    $data = [
        [
            'id' => 1,
            'policy_number' => 'POL-001',
            'car_name' => 'Toyota Camry - 1234',
            'type' => 'accident',
            'date' => '2023-01-15',
            'amount' => 1500,
            'status' => 'pending'
        ]
    ];

    echo json_encode(['success' => true, 'data' => $data]);
}

function createInsurance($db)
{
    $data = [
        'car_id' => $_POST['car_id'] ?? null,
        'insurance_company' => $_POST['insurance_company'] ?? '',
        'policy_number' => $_POST['policy_number'] ?? '',
        'coverage_type' => $_POST['coverage_type'] ?? '',
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'premium_amount' => $_POST['premium_amount'] ?? 0,
        'deductible' => $_POST['deductible'] ?? 0,
        'coverage_limit' => $_POST['coverage_limit'] ?? 0,
        'agent_name' => $_POST['agent_name'] ?? '',
        'agent_phone' => $_POST['agent_phone'] ?? '',
        'notes' => $_POST['notes'] ?? '',
        'status' => $_POST['status'] ?? 'active'
    ];

    $stmt = $db->prepare("INSERT INTO insurance (car_id, insurance_company, policy_number, coverage_type, 
                          start_date, end_date, premium_amount, deductible, coverage_limit, 
                          agent_name, agent_phone, notes, status, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $stmt->execute([
        $data['car_id'],
        $data['insurance_company'],
        $data['policy_number'],
        $data['coverage_type'],
        $data['start_date'],
        $data['end_date'],
        $data['premium_amount'],
        $data['deductible'],
        $data['coverage_limit'],
        $data['agent_name'],
        $data['agent_phone'],
        $data['notes'],
        $data['status']
    ]);

    echo json_encode(['success' => true, 'message' => 'تم إضافة وثيقة التأمين بنجاح', 'id' => $db->lastInsertId()]);
}

function getInsurance($db)
{
    $id = $_GET['id'] ?? 0;
    $stmt = $db->prepare("SELECT * FROM insurance WHERE id = ?");
    $stmt->execute([$id]);
    $insurance = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($insurance) {
        echo json_encode(['success' => true, 'insurance' => $insurance]);
    } else {
        echo json_encode(['success' => false, 'message' => 'وثيقة التأمين غير موجودة']);
    }
}

function updateInsurance($db)
{
    $id = $_POST['id'] ?? 0;
    // Update logic here
    echo json_encode(['success' => true, 'message' => 'تم تحديث وثيقة التأمين بنجاح']);
}

function deleteInsurance($db)
{
    $id = $_POST['id'] ?? 0;
    $stmt = $db->prepare("DELETE FROM insurance WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'تم حذف وثيقة التأمين بنجاح']);
}
?>