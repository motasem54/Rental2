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
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];

                // Update last login
                $updateStmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);

                // Log login activity
                $this->logActivity($user['id'], 'login', 'تسجيل دخول ناجح');

                return ['success' => true, 'user' => $user];
            }

            // Log failed login attempt
            $this->logSecurityEvent(null, 'failed_login', 'محاولة تسجيل دخول فاشلة: ' . $username);

            return ['success' => false, 'message' => 'بيانات الدخول غير صحيحة'];
        } catch (Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'حدث خطأ أثناظ تسجيل الدخول'];
        }
    }

    public function register($data)
    {
        try {
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
                $data['phone'] ?? null
            ]);

            if ($result) {
                $userId = $this->db->lastInsertId();

                // Log registration
                $this->logActivity($userId, 'register', 'تسجيل مستخدم جديد');

                // Send WhatsApp welcome message (if WhatsApp is configured)
                if (isset($data['phone']) && class_exists('WhatsAppAPI')) {
                    try {
                        $whatsapp = new WhatsAppAPI();
                        $whatsapp->sendWelcomeMessage($data['phone'], $data['full_name']);
                    } catch (Exception $e) {
                        error_log('WhatsApp error: ' . $e->getMessage());
                    }
                }

                return ['success' => true, 'user_id' => $userId];
            }

            return ['success' => false, 'message' => 'فشل في إنشاء الحساب'];
        } catch (Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'حدث خطأ أثناظ التسجيل'];
        }
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public function isAdmin()
    {
        return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
    }

    public function isEmployee()
    {
        return isset($_SESSION['role']) && $_SESSION['role'] == 'employee';
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

        // Role-based permissions
        $role = $_SESSION['role'] ?? 'customer';

        $permissions = [
            'admin' => ['*'], // All permissions
            'employee' => [
                'view_dashboard',
                'view_bookings',
                'create_booking',
                'edit_booking',
                'view_cars',
                'view_customers',
                'view_reports',
                'view_payments'
            ],
            'customer' => [
                'view_own_bookings',
                'create_booking',
                'view_cars'
            ]
        ];

        $rolePermissions = $permissions[$role] ?? [];

        // Check if has all permissions
        if (in_array('*', $rolePermissions)) {
            return true;
        }

        return in_array($permission, $rolePermissions);
    }

    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log('Get current user error: ' . $e->getMessage());
            return null;
        }
    }

    public function logout()
    {
        if (isset($_SESSION['user_id'])) {
            $this->logActivity($_SESSION['user_id'], 'logout', 'تسجيل خروج');
        }

        session_destroy();
        return ['success' => true, 'message' => 'تم تسجيل الخروج بنجاح'];
    }

    /**
     * Log activity to activity_log table
     */
    private function logActivity($userId, $action, $description)
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO activity_log (user_id, action, description, ip_address, user_agent, created_at) 
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            
            $stmt->execute([
                $userId,
                $action,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            // Don't throw - logging should not break the app
            error_log('Activity log error: ' . $e->getMessage());
        }
    }

    /**
     * Log security events to security_logs table
     */
    private function logSecurityEvent($userId, $eventType, $description, $severity = 'medium')
    {
        try {
            // Check if security_logs table exists
            $tableCheck = $this->db->query(
                "SELECT 1 FROM information_schema.tables 
                 WHERE table_schema = DATABASE() 
                 AND table_name = 'security_logs'"
            );

            if ($tableCheck && $tableCheck->rowCount() > 0) {
                $stmt = $this->db->prepare(
                    "INSERT INTO security_logs (user_id, event_type, description, ip_address, user_agent, severity, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, NOW())"
                );
                
                $stmt->execute([
                    $userId,
                    $eventType,
                    $description,
                    $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                    $severity
                ]);
            }
        } catch (Exception $e) {
            error_log('Security log error: ' . $e->getMessage());
        }
    }

    /**
     * Change user password
     */
    public function changePassword($userId, $oldPassword, $newPassword)
    {
        try {
            // Verify old password
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($oldPassword, $user['password'])) {
                return ['success' => false, 'message' => 'كلمة المرور القديمة غير صحيحة'];
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $result = $updateStmt->execute([$hashedPassword, $userId]);

            if ($result) {
                $this->logActivity($userId, 'password_change', 'تغيير كلمة المرور');
                $this->logSecurityEvent($userId, 'password_change', 'تم تغيير كلمة المرور بنجاح', 'low');
                return ['success' => true, 'message' => 'تم تغيير كلمة المرور بنجاح'];
            }

            return ['success' => false, 'message' => 'فشل في تغيير كلمة المرور'];
        } catch (Exception $e) {
            error_log('Change password error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'حدث خطأ أثناء تغيير كلمة المرور'];
        }
    }
}
?>