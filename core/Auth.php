<?php
// core/Auth.php

class Auth
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function login($username, $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];

            // Log login activity
            $this->logActivity($user['id'], 'تسجيل دخول', 'قام المستخدم بتسجيل الدخول');

            return ['success' => true, 'user' => $user];
        }

        return ['success' => false, 'message' => 'بيانات الدخول غير صحيحة'];
    }

    public function register($data)
    {
        // Check if user exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$data['username'], $data['email']]);

        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'اسم المستخدم أو البريد الإلكتروني موجود بالفعل'];
        }

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insert user
        $sql = "INSERT INTO users (username, password, email, full_name, phone, role, created_at) 
                VALUES (?, ?, ?, ?, ?, 'customer', NOW())";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['username'],
            $hashedPassword,
            $data['email'],
            $data['full_name'],
            $data['phone']
        ]);

        if ($result) {
            $userId = $this->db->lastInsertId();

            // Send WhatsApp welcome message
            $whatsapp = new WhatsAppAPI();
            $whatsapp->sendWelcomeMessage($data['phone'], $data['full_name']);

            return ['success' => true, 'user_id' => $userId];
        }

        return ['success' => false, 'message' => 'فشل في إنشاء الحساب'];
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public function isAdmin()
    {
        return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
    }

    public function hasPermission($permission)
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        // Admin has all permissions
        if ($this->isAdmin()) {
            return true;
        }

        // Check if permissions exist in session, otherwise fetch from DB
        // For now, allow basic access or check role logic
        // Simple role-based defaults
        $role = $_SESSION['role'] ?? 'customer';

        switch ($permission) {
            case 'view_reports':
                return $role == 'admin' || $role == 'employee';
            case 'manage_settings':
                return $role == 'admin';
            case 'manage_users':
                return $role == 'admin';
            default:
                return false;
        }
    }

    public function logout()
    {
        if (isset($_SESSION['user_id'])) {
            $this->logActivity($_SESSION['user_id'], 'تسجيل خروج', 'قام المستخدم بتسجيل الخروج');
        }

        session_destroy();
        return ['success' => true, 'message' => 'تم تسجيل الخروج بنجاح'];
    }

    private function logActivity($userId, $action, $details)
    {
        $stmt = $this->db->prepare("INSERT INTO activity_log (user_id, action, details, ip_address, created_at) 
                                   VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $action, $details, $_SERVER['REMOTE_ADDR']]);
    }
}
?>