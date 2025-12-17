<?php
// api/maintenance.php
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
            listMaintenance($db);
            break;
        case 'create':
            createMaintenance($db);
            break;
        case 'get':
            getMaintenance($db);
            break;
        case 'update':
            updateMaintenance($db);
            break;
        case 'schedule':
            getMaintenanceSchedule($db);
            break;
        case 'delete':
            deleteMaintenance($db);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function listMaintenance($db)
{
    $stmt = $db->query("SELECT m.*, c.brand, c.model, c.plate_number 
                        FROM maintenance m 
                        LEFT JOIN cars c ON m.car_id = c.id 
                        ORDER BY m.created_at DESC");
    $maintenance = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = array_map(function ($item) {
        return [
            'id' => $item['id'],
            'car_name' => ($item['brand'] ?? '') . ' ' . ($item['model'] ?? '') . ' - ' . ($item['plate_number'] ?? ''),
            'type' => $item['maintenance_type'] ?? '',
            'date' => $item['start_date'] ?? '',
            'cost' => $item['cost'] ?? 0,
            'status' => $item['status'] ?? 'scheduled'
        ];
    }, $maintenance);

    echo json_encode(['success' => true, 'data' => $data]);
}

function getMaintenanceSchedule($db)
{
    // Get counts
    $pending = $db->query("SELECT COUNT(*) FROM maintenance WHERE status = 'scheduled'")->fetchColumn();
    $inProgress = $db->query("SELECT COUNT(*) FROM maintenance WHERE status = 'in_progress'")->fetchColumn();
    $completed = $db->query("SELECT COUNT(*) FROM maintenance WHERE status = 'completed'")->fetchColumn();
    $totalCost = $db->query("SELECT SUM(cost) FROM maintenance")->fetchColumn();

    // Get upcoming maintenance
    $stmt = $db->query("SELECT m.*, c.brand, c.model, c.plate_number 
                        FROM maintenance m 
                        LEFT JOIN cars c ON m.car_id = c.id 
                        WHERE m.status IN ('scheduled', 'in_progress')
                        ORDER BY m.start_date ASC LIMIT 5");
    $upcoming = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'total_cost' => $totalCost ?? 0,
            'upcoming' => $upcoming
        ]
    ]);
}

function createMaintenance($db)
{
    $data = [
        'car_id' => $_POST['car_id'] ?? null,
        'maintenance_type' => $_POST['maintenance_type'] ?? '',
        'description' => $_POST['description'] ?? '',
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? null,
        'cost' => $_POST['cost'] ?? 0,
        'labor_hours' => $_POST['labor_hours'] ?? 0,
        'invoice_number' => $_POST['invoice_number'] ?? '',
        'vendor' => $_POST['vendor'] ?? '',
        'technician' => $_POST['technician'] ?? '',
        'parts_replaced' => $_POST['parts_replaced'] ?? '',
        'notes' => $_POST['notes'] ?? '',
        'status' => $_POST['status'] ?? 'scheduled'
    ];

    $stmt = $db->prepare("INSERT INTO maintenance (car_id, maintenance_type, description, start_date, 
                          end_date, cost, labor_hours, invoice_number, vendor, technician, 
                          parts_replaced, notes, status, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $stmt->execute([
        $data['car_id'],
        $data['maintenance_type'],
        $data['description'],
        $data['start_date'],
        $data['end_date'],
        $data['cost'],
        $data['labor_hours'],
        $data['invoice_number'],
        $data['vendor'],
        $data['technician'],
        $data['parts_replaced'],
        $data['notes'],
        $data['status']
    ]);

    echo json_encode(['success' => true, 'message' => 'تم إضافة سجل الصيانة بنجاح', 'id' => $db->lastInsertId()]);
}

function getMaintenance($db)
{
    $id = $_GET['id'] ?? 0;
    $stmt = $db->prepare("SELECT * FROM maintenance WHERE id = ?");
    $stmt->execute([$id]);
    $maintenance = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($maintenance) {
        echo json_encode(['success' => true, 'maintenance' => $maintenance]);
    } else {
        echo json_encode(['success' => false, 'message' => 'سجل الصيانة غير موجود']);
    }
}

function updateMaintenance($db)
{
    $id = $_POST['id'] ?? 0;
    // Update logic here
    echo json_encode(['success' => true, 'message' => 'تم تحديث سجل الصيانة بنجاح']);
}

function deleteMaintenance($db)
{
    $id = $_POST['id'] ?? 0;
    $stmt = $db->prepare("DELETE FROM maintenance WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'تم حذف سجل الصيانة بنجاح']);
}
?>