<?php
// api/settings.php
// API لإدارة الإعدادات
session_start();
require_once '../config/database.php';
require_once '../core/Auth.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit;
}

class SettingsManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Get settings by category
    public function getSettings($category) {
        $query = "SELECT setting_key, setting_value FROM system_settings WHERE category = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$category]);
        
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    }
    
    // Save settings
    public function saveSettings($category, $settings) {
        $this->pdo->beginTransaction();
        
        try {
            foreach ($settings as $key => $value) {
                $query = "INSERT INTO system_settings (category, setting_key, setting_value, updated_at)
                          VALUES (?, ?, ?, NOW())
                          ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()";
                
                $stmt = $this->pdo->prepare($query);
                $stmt->execute([$category, $key, $value]);
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}

$manager = new SettingsManager($pdo);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    case 'get_general':
        $settings = $manager->getSettings('general');
        echo json_encode(['success' => true, 'settings' => $settings]);
        break;
        
    case 'get_appearance':
        $settings = $manager->getSettings('appearance');
        echo json_encode(['success' => true, 'settings' => $settings]);
        break;
        
    case 'get_financial':
        $settings = $manager->getSettings('financial');
        echo json_encode(['success' => true, 'settings' => $settings]);
        break;
        
    case 'get_whatsapp':
        $settings = $manager->getSettings('whatsapp');
        echo json_encode(['success' => true, 'settings' => $settings]);
        break;
        
    case 'save_general':
        unset($_POST['action']);
        $result = $manager->saveSettings('general', $_POST);
        echo json_encode(['success' => $result, 'message' => $result ? 'تم الحفظ بنجاح' : 'فشل الحفظ']);
        break;
        
    case 'save_appearance':
        unset($_POST['action']);
        $result = $manager->saveSettings('appearance', $_POST);
        echo json_encode(['success' => $result, 'message' => $result ? 'تم الحفظ بنجاح' : 'فشل الحفظ']);
        break;
        
    case 'save_financial':
        unset($_POST['action']);
        $result = $manager->saveSettings('financial', $_POST);
        echo json_encode(['success' => $result, 'message' => $result ? 'تم الحفظ بنجاح' : 'فشل الحفظ']);
        break;
        
    case 'save_whatsapp':
        unset($_POST['action']);
        $result = $manager->saveSettings('whatsapp', $_POST);
        echo json_encode(['success' => $result, 'message' => $result ? 'تم الحفظ بنجاح' : 'فشل الحفظ']);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'إجراء غير صحيح']);
}
?>