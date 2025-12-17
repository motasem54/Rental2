<?php
// config/database.php

define('DB_HOST', 'localhost');
define('DB_NAME', 'leadership_rent_demo');
define('DB_USER', 'leadership_rent_demo');
define('DB_PASS', 'V8a2sE5CXDJpQUxZNtw9');

// WhatsApp API Configuration
define('WHATSAPP_TOKEN', 'YOUR_WHATSAPP_TOKEN');
define('WHATSAPP_PHONE_ID', 'YOUR_PHONE_ID');
define('WHATSAPP_API_URL', 'https://graph.facebook.com/v18.0/');

// System Settings
define('CURRENCY', '₪');
define('TAX_RATE', 17);
define('INSURANCE_RATE', 8);
define('LATE_FEE_PERCENT', 25);
define('DEPOSIT_PERCENT', 40);

// Maintenance Settings
define('MAINTENANCE_COST_PER_DAY', 15); // تكلفة الصيانة اليومية
define('MAINTENANCE_ALERT_DAYS', 7);

// Paths
define('BASE_URL', 'https://leadership.ps/RentDemoP');
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');

// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Gaza');

// AutoLoader
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../core/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Include Core Classes
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/AjaxHandler.php';
require_once __DIR__ . '/../core/WhatsAppAPI.php';
?>