<?php
// api/logs.php
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

$limit = intval($_GET['limit'] ?? 10);

// Check if table exists first to avoid errors
try {
    $stmt = $db->prepare("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    $logs = $stmt->fetchAll();

    echo json_encode($logs);
} catch (Exception $e) {
    // If table doesn't exist, return empty array
    echo json_encode([]);
}
?>