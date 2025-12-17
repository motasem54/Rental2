<?php
// core/Permissions.php

class Permissions {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * التحقق من صلاحية المستخدم
     */
    public function hasPermission($user_id, $permission_key) {
        // الحصول على دور المستخدم
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return false;
        }
        
        $role = $user['role'];
        
        // الأدوار الرئيسية
        $roles = [
            'super_admin' => ['*'],  // كل الصلاحيات
            'admin' => [
                'view_dashboard',
                'manage_cars',
                'manage_customers',
                'manage_rentals',
                'manage_payments',
                'manage_maintenance',
                'manage_insurance',
                'view_reports',
                'manage_settings',
                'manage_users',
                'export_data',
                'print_contracts'
            ],
            'manager' => [
                'view_dashboard',
                'manage_cars',
                'manage_customers',
                'manage_rentals',
                'manage_payments',
                'view_reports',
                'export_data',
                'print_contracts'
            ],
            'employee' => [
                'view_dashboard',
                'manage_rentals',
                'manage_payments',
                'print_contracts'
            ],
            'customer' => [
                'view_profile',
                'view_rentals',
                'make_payments'
            ]
        ];
        
        // إذا كان الدور غير موجود
        if (!isset($roles[$role])) {
            return false;
        }
        
        // التحقق من الصلاحية
        if (in_array('*', $roles[$role])) {
            return true;  // جميع الصلاحيات
        }
        
        return in_array($permission_key, $roles[$role]);
    }
    
    /**
     * الحصول على جميع الصلاحيات المتاحة للدور
     */
    public function getRolePermissions($role) {
        $permissions = [
            'dashboard' => [
                'key' => 'view_dashboard',
                'name' => 'عرض لوحة التحكم',
                'description' => 'القدرة على عرض الإحصائيات والرسوم البيانية'
            ],
            'cars_manage' => [
                'key' => 'manage_cars',
                'name' => 'إدارة السيارات',
                'description' => 'إضافة/تعديل/حذف السيارات وعرضها'
            ],
            'cars_view' => [
                'key' => 'view_cars',
                'name' => 'عرض السيارات',
                'description' => 'عرض قائمة السيارات فقط'
            ],
            'customers_manage' => [
                'key' => 'manage_customers',
                'name' => 'إدارة العملاء',
                'description' => 'إضافة/تعديل/حذف العملاء'
            ],
            'rentals_create' => [
                'key' => 'create_rentals',
                'name' => 'إنشاء عمليات تأجير',
                'description' => 'إنشاء عمليات تأجير جديدة'
            ],
            'rentals_manage' => [
                'key' => 'manage_rentals',
                'name' => 'إدارة عمليات التأجير',
                'description' => 'عرض/تعديل/إلغاء عمليات التأجير'
            ],
            'payments_manage' => [
                'key' => 'manage_payments',
                'name' => 'إدارة المدفوعات',
                'description' => 'تسجيل المدفوعات وإنشاء الفواتير'
            ],
            'maintenance_manage' => [
                'key' => 'manage_maintenance',
                'name' => 'إدارة الصيانة',
                'description' => 'جدولة وتتبع عمليات الصيانة'
            ],
            'insurance_manage' => [
                'key' => 'manage_insurance',
                'name' => 'إدارة التأمين',
                'description' => 'إدارة بوالص التأمين والمطالبات'
            ],
            'reports_view' => [
                'key' => 'view_reports',
                'name' => 'عرض التقارير',
                'description' => 'عرض التقارير والإحصائيات'
            ],
            'reports_generate' => [
                'key' => 'generate_reports',
                'name' => 'إنشاء تقارير',
                'description' => 'إنشاء وتصدير التقارير'
            ],
            'settings_manage' => [
                'key' => 'manage_settings',
                'name' => 'إدارة الإعدادات',
                'description' => 'تعديل إعدادات النظام'
            ],
            'users_manage' => [
                'key' => 'manage_users',
                'name' => 'إدارة المستخدمين',
                'description' => 'إضافة/تعديل/حذف المستخدمين'
            ],
            'export_data' => [
                'key' => 'export_data',
                'name' => 'تصدير البيانات',
                'description' => 'تصدير البيانات إلى ملفات Excel/PDF'
            ],
            'print_contracts' => [
                'key' => 'print_contracts',
                'name' => 'طباعة العقود',
                'description' => 'طباعة عقود التأجير والكمبيالات'
            ],
            'backup_manage' => [
                'key' => 'manage_backup',
                'name' => 'إدارة النسخ الاحتياطي',
                'description' => 'إنشاء واستعادة النسخ الاحتياطية'
            ]
        ];
        
        // تصفية الصلاحيات حسب الدور
        $role_permissions = $this->getRolePermissionKeys($role);
        
        $filtered_permissions = [];
        foreach ($permissions as $key => $permission) {
            if (in_array($permission['key'], $role_permissions) || in_array('*', $role_permissions)) {
                $filtered_permissions[$key] = $permission;
            }
        }
        
        return $filtered_permissions;
    }
    
    /**
     * الحصول على مفاتيح الصلاحيات للدور
     */
    private function getRolePermissionKeys($role) {
        $permissions_map = [
            'super_admin' => ['*'],
            'admin' => [
                'view_dashboard',
                'manage_cars',
                'manage_customers',
                'manage_rentals',
                'manage_payments',
                'manage_maintenance',
                'manage_insurance',
                'view_reports',
                'generate_reports',
                'manage_settings',
                'manage_users',
                'export_data',
                'print_contracts',
                'manage_backup'
            ],
            'manager' => [
                'view_dashboard',
                'manage_cars',
                'manage_customers',
                'manage_rentals',
                'manage_payments',
                'view_reports',
                'generate_reports',
                'export_data',
                'print_contracts'
            ],
            'employee' => [
                'view_dashboard',
                'view_cars',
                'create_rentals',
                'manage_rentals',
                'manage_payments',
                'print_contracts'
            ],
            'customer' => [
                'view_profile',
                'view_rentals',
                'make_payments'
            ]
        ];
        
        return $permissions_map[$role] ?? [];
    }
    
    /**
     * التحقق من صلاحية الوصول إلى الملف
     */
    public function checkFileAccess($user_id, $file_type, $file_id = null) {
        // الحصول على دور المستخدم
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return false;
        }
        
        $role = $user['role'];
        
        // قواعد الوصول للأنواع المختلفة من الملفات
        $access_rules = [
            'contract' => ['super_admin', 'admin', 'manager', 'employee'],
            'report' => ['super_admin', 'admin', 'manager'],
            'settings' => ['super_admin', 'admin'],
            'backup' => ['super_admin', 'admin'],
            'user_data' => ['super_admin', 'admin'],
            'financial' => ['super_admin', 'admin', 'manager'],
            'customer_data' => ['super_admin', 'admin', 'manager', 'employee']
        ];
        
        // إذا كان النوع غير موجود في القواعد
        if (!isset($access_rules[$file_type])) {
            return false;
        }
        
        // التحقق من الدور
        if (!in_array($role, $access_rules[$file_type])) {
            return false;
        }
        
        // تحقق إضافي للملفات الحساسة
        if ($file_id) {
            return $this->verifyFileOwnership($user_id, $file_type, $file_id);
        }
        
        return true;
    }
    
    /**
     * التحقق من ملكية الملف
     */
    private function verifyFileOwnership($user_id, $file_type, $file_id) {
        switch ($file_type) {
            case 'contract':
                // التحقق مما إذا كان المستخدم هو من أنشأ العقد
                $stmt = $this->db->prepare("
                    SELECT created_by 
                    FROM rentals 
                    WHERE id = ? AND (created_by = ? OR ? IN ('super_admin', 'admin'))
                ");
                $stmt->execute([$file_id, $user_id, $_SESSION['role']]);
                return $stmt->rowCount() > 0;
                
            case 'report':
                // التقارير متاحة حسب الدور فقط
                return in_array($_SESSION['role'], ['super_admin', 'admin', 'manager']);
                
            default:
                return true;
        }
    }
    
    /**
     * تسجيل محاولة وصول غير مصرح بها
     */
    public function logUnauthorizedAccess($user_id, $action, $details) {
        $stmt = $this->db->prepare("
            INSERT INTO security_logs (user_id, action, details, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $user_id,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
    
    /**
     * إنشاء رمز تحقق للعملية
     */
    public function generateVerificationCode($rental_id, $action) {
        $code = strtoupper(substr(md5(uniqid() . $rental_id . $action), 0, 8));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $this->db->prepare("
            INSERT INTO verification_codes (rental_id, action, code, expires_at, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([$rental_id, $action, $code, $expires_at]);
        
        return $code;
    }
    
    /**
     * التحقق من صحة الرمز
     */
    public function verifyCode($rental_id, $action, $code) {
        $stmt = $this->db->prepare("
            SELECT * FROM verification_codes 
            WHERE rental_id = ? AND action = ? AND code = ? AND expires_at > NOW() AND used = 0
        ");
        
        $stmt->execute([$rental_id, $action, $code]);
        
        if ($stmt->rowCount() > 0) {
            // تحديث حالة الرمز ليكون مستخدماً
            $update_stmt = $this->db->prepare("
                UPDATE verification_codes SET used = 1, used_at = NOW() 
                WHERE rental_id = ? AND action = ? AND code = ?
            ");
            
            $update_stmt->execute([$rental_id, $action, $code]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * التحقق من صلاحية التعديل على عملية التأجير
     */
    public function canModifyRental($user_id, $rental_id) {
        $user_role = $_SESSION['role'] ?? '';
        
        // الأدوار المسموح لها بالتعديل
        if (!in_array($user_role, ['super_admin', 'admin', 'manager'])) {
            return false;
        }
        
        // التحقق من حالة العملية
        $stmt = $this->db->prepare("SELECT rental_status FROM rentals WHERE id = ?");
        $stmt->execute([$rental_id]);
        $rental = $stmt->fetch();
        
        if (!$rental) {
            return false;
        }
        
        // لا يمكن تعديل العمليات المكتملة أو الملغية
        if (in_array($rental['rental_status'], ['completed', 'cancelled'])) {
            // فقط السوبر أدمن يمكنه تعديل العمليات المكتملة
            return $user_role === 'super_admin';
        }
        
        return true;
    }
    
    /**
     * الحصول على قائمة المستخدمين بناءً على الصلاحيات
     */
    public function getUsersByPermission($permission_key) {
        $all_users = $this->getAllUsersWithRoles();
        
        $filtered_users = [];
        foreach ($all_users as $user) {
            if ($this->hasPermission($user['id'], $permission_key)) {
                $filtered_users[] = $user;
            }
        }
        
        return $filtered_users;
    }
    
    /**
     * الحصول على جميع المستخدمين مع أدوارهم
     */
    private function getAllUsersWithRoles() {
        $stmt = $this->db->prepare("SELECT id, username, full_name, role, status FROM users WHERE status = 'active'");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * تحديث صلاحيات المستخدم
     */
    public function updateUserPermissions($user_id, $permissions) {
        // هذا مثال مبسط، في نظام حقيقي قد يكون هناك جدول منفصل لصلاحيات المستخدمين
        $stmt = $this->db->prepare("UPDATE users SET permissions = ? WHERE id = ?");
        $permissions_json = json_encode($permissions);
        return $stmt->execute([$permissions_json, $user_id]);
    }
}
?>