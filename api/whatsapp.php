<?php
// api/whatsapp.php
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

$action = $_POST['action'] ?? '';
$whatsapp = new WhatsAppAPI();

switch ($action) {
    case 'send_reminder':
        $bookingId = intval($_POST['booking_id']);
        // Fetch phone number logic here...
        // For now, mock success
        echo json_encode(['success' => true, 'message' => 'تم إرسال التذكير']);
        break;

    case 'send_rental_reminder':
        $rentalId = intval($_POST['rental_id']);
        // Fetch phone number logic here...
        echo json_encode(['success' => true, 'message' => 'تم إرسال تذكير الإيجار']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action not found']);
}
?>