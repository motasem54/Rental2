<?php
// api/system.php
// API لمعلومات النظام
session_start();
require_once '../config/database.php';
require_once '../core/Auth.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit;
}

class SystemManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Get system information
    public function getSystemInfo() {
        // OS Info
        $os = php_uname('s') . ' ' . php_uname('r');
        
        // MySQL Version
        $mysqlVersion = $this->pdo->query('SELECT VERSION()')->fetchColumn();
        
        // Disk Space
        $diskTotal = disk_total_space('.');
        $diskFree = disk_free_space('.');
        $diskUsed = $diskTotal - $diskFree;
        $diskInfo = round($diskFree / 1024 / 1024 / 1024, 2) . ' GB حر من ' . 
                    round($diskTotal / 1024 / 1024 / 1024, 2) . ' GB';
        
        // Memory Usage
        $memoryUsage = round(memory_get_usage() / 1024 / 1024, 2);
        $memoryLimit = ini_get('memory_limit');
        $memoryInfo = $memoryUsage . ' MB / ' . $memoryLimit;
        
        // Database stats
        $totalUsers = $this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $totalCars = $this->pdo->query('SELECT COUNT(*) FROM cars')->fetchColumn();
        
        return [
            'os' => $os,
            'mysql' => $mysqlVersion,
            'disk' => $diskInfo,
            'memory' => $memoryInfo,
            'install_date' => '2025-01-01',
            'last_update' => date('Y-m-d H:i:s'),
            'total_users' => $totalUsers,
            'total_cars' => $totalCars
        ];
    }
    
    // Get system logs
    public function getSystemLogs($limit = 50) {
        $query = "SELECT * FROM activity_log ORDER BY created_at DESC LIMIT ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Create backup
    public function createBackup() {
        $backupDir = '../backups/';
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $backupDir . $filename;
        
        // Database credentials
        $host = DB_HOST;
        $user = DB_USER;
        $pass = DB_PASS;
        $name = DB_NAME;
        
        // Create backup using mysqldump
        $command = "mysqldump --host={$host} --user={$user} --password={$pass} {$name} > {$filepath}";
        exec($command, $output, $result);
        
        if ($result === 0) {
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'download_url' => '/backups/' . $filename
            ];
        } else {
            return ['success' => false, 'message' => 'فشل إنشاء النسخة الاحتياطية'];
        }
    }
    
    // Clear logs
    public function clearLogs() {
        $query = "TRUNCATE TABLE activity_log";
        $this->pdo->exec($query);
        return true;
    }
    
    // Export logs
    public function exportLogs() {
        $logs = $this->getSystemLogs(1000);
        return json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}

$manager = new SystemManager($pdo);
$action = $_GET['action'] ?? '';

switch($action) {
    case 'get_info':
        $info = $manager->getSystemInfo();
        echo json_encode(['success' => true, 'info' => $info]);
        break;
        
    case 'get_logs':
        $logs = $manager->getSystemLogs();
        echo json_encode(['success' => true, 'logs' => $logs]);
        break;
        
    case 'create_backup':
        $result = $manager->createBackup();
        echo json_encode($result);
        break;
        
    case 'clear_logs':
        $result = $manager->clearLogs();
        echo json_encode(['success' => $result]);
        break;
        
    case 'export_logs':
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="system_logs_' . date('Y-m-d') . '.json"');
        echo $manager->exportLogs();
        exit;
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'إجراء غير صحيح']);
}
?>