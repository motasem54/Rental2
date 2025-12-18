-- ============================================
-- نظام تأجير السيارات المتقدم
-- Complete Database Schema - إصدار 2.0
-- تاريخ: 2025-12-18
-- ============================================

-- حذف الجداول القديمة إذا وجدت (احتياطي)
-- SET FOREIGN_KEY_CHECKS = 0;
-- DROP TABLE IF EXISTS `activity_log`;
-- DROP TABLE IF EXISTS `payments`;
-- DROP TABLE IF EXISTS `notifications`;
-- DROP TABLE IF EXISTS `whatsapp_templates`;
-- DROP TABLE IF EXISTS `system_settings`;
-- DROP TABLE IF EXISTS `rentals`;
-- DROP TABLE IF EXISTS `cars`;
-- DROP TABLE IF EXISTS `customers`;
-- DROP TABLE IF EXISTS `users`;
-- SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- 1. جدول المستخدمين (Users)
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','employee','customer') DEFAULT 'customer',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. جدول العملاء (Customers)
-- ============================================
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `id_number` varchar(20) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text,
  `city` varchar(50) DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `license_expiry` date DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `notes` text,
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_rentals` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_number` (`id_number`),
  KEY `idx_phone` (`phone`),
  KEY `idx_email` (`email`),
  KEY `idx_name` (`first_name`, `last_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. جدول السيارات (Cars)
-- ============================================
CREATE TABLE IF NOT EXISTS `cars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `make` varchar(50) NOT NULL COMMENT 'الماركة',
  `model` varchar(50) NOT NULL COMMENT 'الموديل',
  `year` int(4) NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  `plate_number` varchar(20) NOT NULL,
  `vin` varchar(50) DEFAULT NULL COMMENT 'رقم الشاصي',
  `daily_rate` decimal(10,2) NOT NULL,
  `weekly_rate` decimal(10,2) DEFAULT NULL,
  `monthly_rate` decimal(10,2) DEFAULT NULL,
  `status` enum('available','rented','maintenance','out_of_service') DEFAULT 'available',
  `mileage` int(11) DEFAULT 0 COMMENT 'عداد السرعة',
  `fuel_type` enum('petrol','diesel','electric','hybrid') DEFAULT 'petrol',
  `transmission` enum('manual','automatic') DEFAULT 'manual',
  `seats` int(2) DEFAULT 5,
  `features` text COMMENT 'المميزات بصيغة JSON',
  `images` text COMMENT 'الصور بصيغة JSON',
  `last_maintenance_date` date DEFAULT NULL,
  `last_maintenance_mileage` int(11) DEFAULT 0,
  `next_maintenance_date` date DEFAULT NULL,
  `next_maintenance_mileage` int(11) DEFAULT NULL,
  `insurance_expiry` date DEFAULT NULL,
  `license_expiry` date DEFAULT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plate_number` (`plate_number`),
  KEY `idx_status` (`status`),
  KEY `idx_make_model` (`make`, `model`),
  KEY `idx_maintenance` (`next_maintenance_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. جدول الحجوزات/الإيجارات (Rentals)
-- ============================================
CREATE TABLE IF NOT EXISTS `rentals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `actual_return_date` datetime DEFAULT NULL,
  `pickup_location` varchar(200) DEFAULT NULL,
  `return_location` varchar(200) DEFAULT NULL,
  `starting_mileage` int(11) DEFAULT NULL,
  `ending_mileage` int(11) DEFAULT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `total_days` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `insurance_amount` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `late_penalty` decimal(10,2) DEFAULT 0.00,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `payment_status` enum('unpaid','partial','paid','refunded') DEFAULT 'unpaid',
  `status` enum('pending','confirmed','active','completed','cancelled') DEFAULT 'pending',
  `insurance_type` enum('none','basic','full') DEFAULT 'none',
  `damage_report` text,
  `notes` text,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_car` (`car_id`),
  KEY `idx_status` (`status`),
  KEY `idx_dates` (`start_date`, `end_date`),
  KEY `idx_status_dates` (`status`, `start_date`, `end_date`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `rentals_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rentals_car_fk` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rentals_user_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. جدول الدفعات (Payments)
-- ============================================
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rental_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','credit_card','debit_card','bank_transfer','check') NOT NULL,
  `payment_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `transaction_id` varchar(100) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded','cancelled') DEFAULT 'completed',
  `notes` text,
  `receipt_number` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rental` (`rental_id`),
  KEY `idx_payment_method` (`payment_method`),
  KEY `idx_status` (`status`),
  KEY `idx_payment_date` (`payment_date`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `payments_rental_fk` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_user_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. جدول الإشعارات (Notifications)
-- ============================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','danger','rental_ending','rental_late','maintenance','payment') DEFAULT 'info',
  `related_id` int(11) DEFAULT NULL COMMENT 'ID of related rental/car/etc',
  `related_type` varchar(50) DEFAULT NULL COMMENT 'rental, car, payment, etc',
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_type` (`type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `notifications_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. جدول سجل النشاطات (Activity Log)
-- ============================================
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_table_record` (`table_name`, `record_id`),
  CONSTRAINT `activity_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. جدول الإعدادات (System Settings)
-- ============================================
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting` (`category`, `setting_key`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. جدول قوالب واتساب (WhatsApp Templates)
-- ============================================
CREATE TABLE IF NOT EXISTS `whatsapp_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `language` varchar(10) DEFAULT 'ar',
  `category` enum('booking','payment','reminder','promotion','maintenance') DEFAULT 'booking',
  `content` text NOT NULL,
  `variables` json DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name` (`name`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 10. جدول الصيانة (Maintenance)
-- ============================================
CREATE TABLE IF NOT EXISTS `maintenance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `car_id` int(11) NOT NULL,
  `maintenance_type` enum('routine','repair','inspection','other') DEFAULT 'routine',
  `description` text NOT NULL,
  `cost` decimal(10,2) DEFAULT 0.00,
  `mileage_at_service` int(11) DEFAULT NULL,
  `service_date` date NOT NULL,
  `next_service_date` date DEFAULT NULL,
  `next_service_mileage` int(11) DEFAULT NULL,
  `vendor_name` varchar(100) DEFAULT NULL,
  `vendor_phone` varchar(20) DEFAULT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
  `notes` text,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_car` (`car_id`),
  KEY `idx_type` (`maintenance_type`),
  KEY `idx_status` (`status`),
  KEY `idx_service_date` (`service_date`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `maintenance_car_fk` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE,
  CONSTRAINT `maintenance_user_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- إدراج البيانات الافتراضية
-- ============================================

-- مستخدم إداري افتراضي
INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `role`, `status`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'المدير العام', 'admin@rental.com', 'admin', 'active')
ON DUPLICATE KEY UPDATE `username` = `username`;
-- كلمة المرور: 123456

-- الإعدادات العامة
INSERT INTO `system_settings` (`category`, `setting_key`, `setting_value`, `setting_type`, `description`) VALUES
-- General Settings
('general', 'system_name', 'نظام تأجير السيارات المتقدم', 'string', 'اسم النظام'),
('general', 'company_name', 'شركة تأجير السيارات', 'string', 'اسم الشركة'),
('general', 'company_address', 'فلسطين', 'string', 'عنوان الشركة'),
('general', 'company_phone', '+970599999999', 'string', 'رقم هاتف الشركة'),
('general', 'company_email', 'info@rental.com', 'string', 'بريد الشركة'),
('general', 'company_website', 'https://rental.com', 'string', 'موقع الشركة'),
('general', 'default_language', 'ar', 'string', 'اللغة الافتراضية'),
('general', 'timezone', 'Asia/Gaza', 'string', 'المنطقة الزمنية'),
('general', 'maintenance_mode', '0', 'boolean', 'وضع الصيانة'),

-- Appearance Settings
('appearance', 'primary_color', '#FF5722', 'string', 'اللون الرئيسي'),
('appearance', 'secondary_color', '#121212', 'string', 'اللون الثانوي'),
('appearance', 'dark_mode', '1', 'boolean', 'الوضع الداكن'),
('appearance', 'rtl_mode', '1', 'boolean', 'اتجاه RTL'),
('appearance', 'glass_effect', '1', 'boolean', 'تأثير الزجاج'),
('appearance', 'animations', '1', 'boolean', 'الحركات'),

-- Financial Settings
('financial', 'currency', 'ILS', 'string', 'العملة'),
('financial', 'currency_symbol', '₪', 'string', 'رمز العملة'),
('financial', 'tax_rate', '17.00', 'number', 'معدل الضريبة'),
('financial', 'deposit_percentage', '40.00', 'number', 'نسبة الدفعة المقدمة'),
('financial', 'late_fee_per_day', '50.00', 'number', 'رسوم التأخير اليومي'),
('financial', 'late_fee_per_hour', '10.00', 'number', 'رسوم التأخير بالساعة'),
('financial', 'maintenance_cost_per_day', '15.00', 'number', 'تكلفة الصيانة اليومية'),
('financial', 'max_rental_days', '30', 'number', 'أقصى فترة إيجار'),
('financial', 'full_insurance_rate', '15.00', 'number', 'نسبة التأمين الكامل'),
('financial', 'partial_insurance_rate', '12.00', 'number', 'نسبة التأمين الجزئي'),
('financial', 'third_party_insurance_rate', '8.00', 'number', 'نسبة التأمين ضد الغير'),
('financial', 'auto_generate_invoices', '1', 'boolean', 'توليد فواتير تلقائي'),

-- WhatsApp Settings
('whatsapp', 'whatsapp_enabled', '1', 'boolean', 'تفعيل واتساب'),
('whatsapp', 'whatsapp_token', '', 'string', 'توكن واتساب'),
('whatsapp', 'whatsapp_phone_id', '', 'string', 'رقم هاتف واتساب'),
('whatsapp', 'whatsapp_booking_confirmation', '1', 'boolean', 'تأكيد الحجز'),
('whatsapp', 'whatsapp_payment_reminder', '1', 'boolean', 'تذكير الدفع'),
('whatsapp', 'whatsapp_maintenance_alert', '1', 'boolean', 'تنبيه الصيانة'),
('whatsapp', 'whatsapp_promotional', '0', 'boolean', 'رسائل ترويجية'),

-- Email Settings
('email', 'smtp_host', 'smtp.gmail.com', 'string', 'خادم SMTP'),
('email', 'smtp_port', '587', 'number', 'منفذ SMTP'),
('email', 'smtp_username', '', 'string', 'اسم مستخدم SMTP'),
('email', 'smtp_password', '', 'string', 'كلمة مرور SMTP'),
('email', 'smtp_encryption', 'tls', 'string', 'تشفير SMTP'),
('email', 'from_email', 'noreply@rental.com', 'string', 'البريد الافتراضي'),
('email', 'from_name', 'نظام تأجير السيارات', 'string', 'الاسم الافتراضي'),

-- Security Settings
('security', 'two_factor_auth', '0', 'boolean', 'المصادقة الثنائية'),
('security', 'session_timeout', '60', 'number', 'مدة الجلسة بالدقائق'),
('security', 'max_login_attempts', '5', 'number', 'عدد محاولات الدخول'),
('security', 'password_min_length', '6', 'number', 'الحد الأدنى لطول كلمة المرور'),
('security', 'require_strong_password', '0', 'boolean', 'كلمة مرور قوية')

ON DUPLICATE KEY UPDATE 
  `setting_value` = VALUES(`setting_value`),
  `updated_at` = CURRENT_TIMESTAMP;

-- قوالب واتساب
INSERT INTO `whatsapp_templates` (`name`, `language`, `category`, `content`, `variables`, `status`) VALUES
('booking_confirmation', 'ar', 'booking', 'مرحباً {{customer_name}}،\n\nتم تأكيد حجزك بنجاح! ✅\n\nالسيارة: {{car_name}}\nمن: {{start_date}}\nإلى: {{end_date}}\nالمبلغ: {{amount}} ₪\n\nشكراً لتعاملكم معنا. 🚗', '["customer_name", "car_name", "start_date", "end_date", "amount"]', 'active'),

('payment_reminder', 'ar', 'payment', 'عزيزي {{customer_name}}،\n\nنذكرك بدفع المبلغ المتبقي {{amount}} ₪ للحجز رقم {{booking_id}}.\n\nيرجى الدفع قبل {{due_date}}.\n\nشكراً لتعاونكم. 💳', '["customer_name", "amount", "booking_id", "due_date"]', 'active'),

('late_return', 'ar', 'reminder', 'عزيزي {{customer_name}}،\n\nنأمل منكم إعادة السيارة {{car_name}} في أقرب وقت ممكن. ⏰\n\nغرامة التأخير: {{penalty}} ₪\n\nشكراً لتفهمكم. 🙏', '["customer_name", "car_name", "penalty"]', 'active'),

('maintenance_reminder', 'ar', 'maintenance', 'تنبيه: السيارة {{car_name}} ({{plate_number}}) تحتاج صيانة.\n\nآخر صيانة: {{last_maintenance}}\nالصيانة القادمة: {{next_maintenance}}\n\nيرجى جدولة الصيانة. 🔧', '["car_name", "plate_number", "last_maintenance", "next_maintenance"]', 'active'),

('rental_ending_soon', 'ar', 'reminder', 'عزيزي {{customer_name}}،\n\nتنتهي فترة إيجارك للسيارة {{car_name}} في {{end_date}}.\n\nيرجى إعادة السيارة في الوقت المحدد.\n\nشكراً. 🚗', '["customer_name", "car_name", "end_date"]', 'active')

ON DUPLICATE KEY UPDATE 
  `content` = VALUES(`content`),
  `variables` = VALUES(`variables`),
  `updated_at` = CURRENT_TIMESTAMP;

-- إشعارات ترحيبية
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `created_at`) VALUES
(1, 'مرحباً بك في النظام', 'تم تفعيل نظام الإشعارات بنجاح. ستصلك إشعارات عن جميع الأنشطة المهمة.', 'success', NOW()),
(1, 'تحديث النظام', 'تم تحديث النظام إلى الإصدار 2.0 بنجاح مع إضافة ميزات جديدة.', 'info', NOW())
ON DUPLICATE KEY UPDATE `title` = `title`;

-- ============================================
-- Indexes إضافية لتحسين الأداء
-- ============================================

-- Users indexes
ALTER TABLE `users` ADD INDEX `idx_email_status` (`email`, `status`);
ALTER TABLE `users` ADD INDEX `idx_last_login` (`last_login`);

-- Customers indexes  
ALTER TABLE `customers` ADD INDEX `idx_rating` (`rating`);
ALTER TABLE `customers` ADD INDEX `idx_total_rentals` (`total_rentals`);

-- Cars indexes
ALTER TABLE `cars` ADD INDEX `idx_daily_rate` (`daily_rate`);
ALTER TABLE `cars` ADD INDEX `idx_year` (`year`);
ALTER TABLE `cars` ADD INDEX `idx_fuel_type` (`fuel_type`);

-- Rentals indexes
ALTER TABLE `rentals` ADD INDEX `idx_payment_status` (`payment_status`);
ALTER TABLE `rentals` ADD INDEX `idx_total_amount` (`total_amount`);

-- Payments indexes
ALTER TABLE `payments` ADD INDEX `idx_amount` (`amount`);

-- ============================================
-- Views للاستعلامات الشائعة
-- ============================================

-- عرض الحجوزات النشطة مع التفاصيل
CREATE OR REPLACE VIEW `active_rentals_view` AS
SELECT 
    r.id,
    r.start_date,
    r.end_date,
    r.total_amount,
    r.paid_amount,
    r.payment_status,
    r.late_penalty,
    CONCAT(c.first_name, ' ', c.last_name) as customer_name,
    c.phone as customer_phone,
    CONCAT(car.make, ' ', car.model, ' ', car.year) as car_name,
    car.plate_number,
    u.full_name as created_by_name,
    DATEDIFF(r.end_date, NOW()) as days_remaining,
    CASE 
        WHEN r.end_date < NOW() THEN 'متأخر'
        WHEN DATEDIFF(r.end_date, NOW()) <= 1 THEN 'ينتهي قريباً'
        ELSE 'نشط'
    END as rental_status
FROM rentals r
JOIN customers c ON r.customer_id = c.id
JOIN cars car ON r.car_id = car.id
LEFT JOIN users u ON r.created_by = u.id
WHERE r.status = 'active';

-- عرض السيارات المتاحة
CREATE OR REPLACE VIEW `available_cars_view` AS
SELECT 
    id,
    make,
    model,
    year,
    color,
    plate_number,
    daily_rate,
    weekly_rate,
    monthly_rate,
    mileage,
    fuel_type,
    transmission,
    seats,
    CASE 
        WHEN next_maintenance_date <= DATE_ADD(NOW(), INTERVAL 7 DAY) THEN 'يحتاج صيانة قريباً'
        ELSE 'جاهز'
    END as maintenance_status
FROM cars
WHERE status = 'available';

-- ============================================
-- Stored Procedures للعمليات المعقدة
-- ============================================

DELIMITER //

-- حساب الغرامات التلقائي
CREATE PROCEDURE IF NOT EXISTS `calculate_late_penalties`()
BEGIN
    UPDATE rentals
    SET late_penalty = CASE
        WHEN TIMESTAMPDIFF(DAY, end_date, NOW()) > 0 THEN
            TIMESTAMPDIFF(DAY, end_date, NOW()) * 500
        WHEN TIMESTAMPDIFF(HOUR, end_date, NOW()) > 0 THEN
            TIMESTAMPDIFF(HOUR, end_date, NOW()) * 50
        ELSE 0
    END
    WHERE status = 'active' AND end_date < NOW();
END//

-- تحديث حالة السيارات
CREATE PROCEDURE IF NOT EXISTS `update_car_status`()
BEGIN
    -- تحديث السيارات المؤجرة
    UPDATE cars 
    SET status = 'rented'
    WHERE id IN (
        SELECT DISTINCT car_id 
        FROM rentals 
        WHERE status = 'active'
    );
    
    -- تحديث السيارات المتاحة
    UPDATE cars
    SET status = 'available'
    WHERE id NOT IN (
        SELECT DISTINCT car_id 
        FROM rentals 
        WHERE status = 'active'
    ) AND status = 'rented';
END//

DELIMITER ;

-- ============================================
-- Triggers للعمليات التلقائية
-- ============================================

DELIMITER //

-- تسجيل النشاطات عند إضافة حجز
CREATE TRIGGER IF NOT EXISTS `after_rental_insert`
AFTER INSERT ON `rentals`
FOR EACH ROW
BEGIN
    INSERT INTO activity_log (user_id, action, description, table_name, record_id, new_values)
    VALUES (
        NEW.created_by,
        'create',
        CONCAT('تم إضافة حجز جديد #', NEW.id),
        'rentals',
        NEW.id,
        JSON_OBJECT(
            'customer_id', NEW.customer_id,
            'car_id', NEW.car_id,
            'total_amount', NEW.total_amount
        )
    );
    
    -- تحديث عدد الحجوزات للعميل
    UPDATE customers 
    SET total_rentals = total_rentals + 1
    WHERE id = NEW.customer_id;
END//

-- تسجيل النشاطات عند تحديث حجز
CREATE TRIGGER IF NOT EXISTS `after_rental_update`
AFTER UPDATE ON `rentals`
FOR EACH ROW
BEGIN
    INSERT INTO activity_log (user_id, action, description, table_name, record_id, old_values, new_values)
    VALUES (
        NEW.created_by,
        'update',
        CONCAT('تم تحديث الحجز #', NEW.id),
        'rentals',
        NEW.id,
        JSON_OBJECT('status', OLD.status, 'total_amount', OLD.total_amount),
        JSON_OBJECT('status', NEW.status, 'total_amount', NEW.total_amount)
    );
END//

DELIMITER ;

-- ============================================
-- Events للمهام الدورية
-- ============================================

-- تفعيل Event Scheduler
SET GLOBAL event_scheduler = ON;

-- حساب الغرامات كل ساعة
CREATE EVENT IF NOT EXISTS `hourly_penalty_calculation`
ON SCHEDULE EVERY 1 HOUR
DO
  CALL calculate_late_penalties();

-- تحديث حالة السيارات كل 30 دقيقة
CREATE EVENT IF NOT EXISTS `update_car_status_event`
ON SCHEDULE EVERY 30 MINUTE
DO
  CALL update_car_status();

-- حذف الإشعارات القديمة (أكثر من 90 يوم) كل أسبوع
CREATE EVENT IF NOT EXISTS `cleanup_old_notifications`
ON SCHEDULE EVERY 1 WEEK
DO
  DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY) AND is_read = 1;

-- ============================================
-- النهاية - Database Schema Complete
-- ============================================

-- عرض ملخص الجداول
SELECT 
    TABLE_NAME as 'جدول',
    TABLE_ROWS as 'عدد السجلات',
    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) as 'الحجم (MB)'
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC;