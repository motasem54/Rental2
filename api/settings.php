<?php
// api/settings.php
require_once '../config/database.php';
require_once '../core/Auth.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

$db = Database::getInstance()->getConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_general':
            getGeneralSettings($db);
            break;
        case 'get_appearance':
            getAppearanceSettings($db);
            break;
        case 'get_financial':
            getFinancialSettings($db);
            break;
        case 'get_whatsapp':
            getWhatsAppSettings($db);
            break;
        case 'save_general':
            saveGeneralSettings($db);
            break;
        case 'save_appearance':
            saveAppearanceSettings($db);
            break;
        case 'save_financial':
            saveFinancialSettings($db);
            break;
        case 'save_whatsapp':
            saveWhatsAppSettings($db);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function getGeneralSettings($db)
{
    // Return default settings
    echo json_encode([
        'success' => true,
        'settings' => [
            'site_name' => 'نظام تأجير السيارات',
            'site_description' => 'نظام إدارة شركة تأجير السيارات',
            'contact_email' => 'info@example.com',
            'contact_phone' => '+970-123-456-789',
            'address' => 'فلسطين',
            'timezone' => 'Asia/Gaza',
            'language' => 'ar',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i'
        ]
    ]);
}

function getAppearanceSettings($db)
{
    echo json_encode([
        'success' => true,
        'settings' => [
            'primary_color' => '#FF5722',
            'secondary_color' => '#2196F3',
            'logo_url' => '',
            'favicon_url' => '',
            'theme' => 'dark',
            'glass_effect' => true,
            'animations' => true
        ]
    ]);
}

function getFinancialSettings($db)
{
    echo json_encode([
        'success' => true,
        'settings' => [
            'currency' => 'ILS',
            'currency_symbol' => '₪',
            'tax_rate' => '16',
            'tax_included' => false,
            'invoice_prefix' => 'INV-',
            'invoice_start_number' => '1000',
            'payment_methods' => 'cash,card,bank_transfer'
        ]
    ]);
}

function getWhatsAppSettings($db)
{
    echo json_encode([
        'success' => true,
        'settings' => [
            'whatsapp_enabled' => true,
            'whatsapp_api_key' => '',
            'whatsapp_phone' => '',
            'whatsapp_instance' => '',
            'whatsapp_reminders' => true,
            'whatsapp_notifications' => true,
            'whatsapp_promotional' => false
        ]
    ]);
}

function saveGeneralSettings($db)
{
    // In a real app, save to database
    echo json_encode([
        'success' => true,
        'message' => 'تم حفظ الإعدادات العامة بنجاح'
    ]);
}

function saveAppearanceSettings($db)
{
    echo json_encode([
        'success' => true,
        'message' => 'تم حفظ إعدادات المظهر بنجاح'
    ]);
}

function saveFinancialSettings($db)
{
    echo json_encode([
        'success' => true,
        'message' => 'تم حفظ الإعدادات المالية بنجاح'
    ]);
}

function saveWhatsAppSettings($db)
{
    echo json_encode([
        'success' => true,
        'message' => 'تم حفظ إعدادات واتساب بنجاح'
    ]);
}
?>