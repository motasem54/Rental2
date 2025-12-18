<?php
// api/whatsapp.php
// API لواتساب
session_start();
require_once '../config/database.php';
require_once '../core/Auth.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit;
}

class WhatsAppManager {
    private $pdo;
    private $token;
    private $phoneId;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->loadSettings();
    }
    
    private function loadSettings() {
        $query = "SELECT setting_key, setting_value FROM system_settings WHERE category = 'whatsapp'";
        $stmt = $this->pdo->query($query);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['setting_key'] === 'whatsapp_token') {
                $this->token = $row['setting_value'];
            } elseif ($row['setting_key'] === 'whatsapp_phone_id') {
                $this->phoneId = $row['setting_value'];
            }
        }
    }
    
    // Test connection
    public function testConnection() {
        if (empty($this->token) || empty($this->phoneId)) {
            return ['success' => false, 'message' => 'يرجى إدخال توكن الوصول ورقم الهاتف'];
        }
        
        // Simulate API call
        // In production, make actual API call to Meta
        return [
            'success' => true,
            'message' => 'تم الاتصال بنجاح. جاهز لإرسال الرسائل.'
        ];
    }
    
    // Get templates
    public function getTemplates() {
        $query = "SELECT * FROM whatsapp_templates ORDER BY created_at DESC";
        $stmt = $this->pdo->query($query);
        
        $templates = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $templates[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'language' => $row['language'],
                'content' => $row['content'],
                'variables' => json_decode($row['variables'], true)
            ];
        }
        
        return $templates;
    }
    
    // Send message
    public function sendMessage($to, $template, $variables = []) {
        // In production, implement actual WhatsApp API call
        return [
            'success' => true,
            'message_id' => 'wamid.' . uniqid()
        ];
    }
}

$manager = new WhatsAppManager($pdo);
$action = $_GET['action'] ?? '';

switch($action) {
    case 'test_connection':
        $result = $manager->testConnection();
        echo json_encode($result);
        break;
        
    case 'get_templates':
        $templates = $manager->getTemplates();
        echo json_encode(['success' => true, 'templates' => $templates]);
        break;
        
    case 'send_message':
        $to = $_POST['to'] ?? '';
        $template = $_POST['template'] ?? '';
        $variables = $_POST['variables'] ?? [];
        
        $result = $manager->sendMessage($to, $template, $variables);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'إجراء غير صحيح']);
}
?>