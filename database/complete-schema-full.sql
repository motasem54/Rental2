-- ============================================
-- نظام تأجير السيارات المتقدم
-- Complete Database Schema - ALL 22 TABLES
-- تاريخ: 2025-12-18
-- ============================================

-- حذف الجداول القديمة إذا وجدت (احتياطي - معطل افتراضياً)
-- SET FOREIGN_KEY_CHECKS = 0;
-- [قائمة الحذف هنا إذا احتجت]
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
-- 2. جدول العملاء (Customers) - من جدول bookings
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
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. جدول السيارات (Cars)
-- ============================================
CREATE TABLE IF NOT EXISTS `cars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` int(4) NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  `plate_number` varchar(20) NOT NULL,
  `vin` varchar(50) DEFAULT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `weekly_rate` decimal(10,2) DEFAULT NULL,
  `monthly_rate` decimal(10,2) DEFAULT NULL,
  `status` enum('available','rented','maintenance','out_of_service') DEFAULT 'available',
  `mileage` int(11) DEFAULT 0,
  `fuel_type` enum('petrol','diesel','electric','hybrid') DEFAULT 'petrol',
  `transmission` enum('manual','automatic') DEFAULT 'manual',
  `seats` int(2) DEFAULT 5,
  `features` text,
  `images` text,
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
  KEY `idx_make_model` (`make`, `model`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. جدول الحجوزات (Bookings)
-- ============================================
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `car_id` int(11) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_car_id` (`car_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `bookings_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bookings_car_fk` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. جدول الإيجارات (Rentals)
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
  CONSTRAINT `rentals_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rentals_car_fk` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. جدول الدفعات (Payments)
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
  CONSTRAINT `payments_rental_fk` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. جدول الإشعارات (Notifications)
-- ============================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','danger','rental_ending','rental_late','maintenance','payment') DEFAULT 'info',
  `related_id` int(11) DEFAULT NULL,
  `related_type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_type` (`type`),
  CONSTRAINT `notifications_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. جدول سجل النشاطات (Activity Log)
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
  CONSTRAINT `activity_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. جدول الإعدادات (Settings)
-- ============================================
CREATE TABLE IF NOT EXISTS `settings` (
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
-- 10. جدول القوالب (Templates)
-- ============================================
CREATE TABLE IF NOT EXISTS `templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` enum('email','sms','whatsapp','contract','invoice') NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `variables` json DEFAULT NULL,
  `language` varchar(10) DEFAULT 'ar',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name_type` (`name`, `type`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 11. جدول الصيانة (Maintenance)
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
  CONSTRAINT `maintenance_car_fk` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 12. جدول التأمين (Insurance)
-- ============================================
CREATE TABLE IF NOT EXISTS `insurance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `car_id` int(11) NOT NULL,
  `policy_number` varchar(100) NOT NULL,
  `provider_name` varchar(100) NOT NULL,
  `provider_phone` varchar(20) DEFAULT NULL,
  `coverage_type` enum('comprehensive','third_party','collision') NOT NULL,
  `coverage_amount` decimal(10,2) NOT NULL,
  `premium_amount` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_car` (`car_id`),
  KEY `idx_policy` (`policy_number`),
  KEY `idx_status` (`status`),
  KEY `idx_expiry` (`expiry_date`),
  CONSTRAINT `insurance_car_fk` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 13. جدول مطالبات التأمين (Insurance Claims)
-- ============================================
CREATE TABLE IF NOT EXISTS `insurance_claims` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `insurance_id` int(11) NOT NULL,
  `rental_id` int(11) DEFAULT NULL,
  `claim_number` varchar(100) NOT NULL,
  `claim_date` date NOT NULL,
  `incident_date` date NOT NULL,
  `incident_description` text NOT NULL,
  `claim_amount` decimal(10,2) NOT NULL,
  `approved_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('submitted','under_review','approved','rejected','paid') DEFAULT 'submitted',
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `claim_number` (`claim_number`),
  KEY `idx_insurance` (`insurance_id`),
  KEY `idx_rental` (`rental_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `claims_insurance_fk` FOREIGN KEY (`insurance_id`) REFERENCES `insurance` (`id`) ON DELETE CASCADE,
  CONSTRAINT `claims_rental_fk` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 14. جدول الرسوم الإضافية (Extra Charges)
-- ============================================
CREATE TABLE IF NOT EXISTS `extra_charges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rental_id` int(11) NOT NULL,
  `charge_type` enum('fuel','cleaning','damage','late_return','toll','parking','other') NOT NULL,
  `description` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `charge_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','paid','waived') DEFAULT 'pending',
  `notes` text,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rental` (`rental_id`),
  KEY `idx_type` (`charge_type`),
  KEY `idx_status` (`status`),
  CONSTRAINT `charges_rental_fk` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 15. جدول التقارير المالية (Financial Reports)
-- ============================================
CREATE TABLE IF NOT EXISTS `financial_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_type` enum('daily','weekly','monthly','yearly','custom') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_revenue` decimal(10,2) DEFAULT 0.00,
  `total_expenses` decimal(10,2) DEFAULT 0.00,
  `net_profit` decimal(10,2) DEFAULT 0.00,
  `total_rentals` int(11) DEFAULT 0,
  `total_payments` decimal(10,2) DEFAULT 0.00,
  `data` json DEFAULT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`report_type`),
  KEY `idx_dates` (`start_date`, `end_date`),
  KEY `idx_generated_by` (`generated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 16. جدول التقارير (Reports)
-- ============================================
CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `report_type` varchar(50) NOT NULL,
  `description` text,
  `filters` json DEFAULT NULL,
  `data` json DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`report_type`),
  KEY `idx_generated_by` (`generated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 17. جدول سجلات الإيجار (Rental Logs)
-- ============================================
CREATE TABLE IF NOT EXISTS `rental_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rental_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rental` (`rental_id`),
  KEY `idx_action` (`action`),
  CONSTRAINT `rental_logs_rental_fk` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 18. جدول سجلات النسخ الاحتياطي (Backup Logs)
-- ============================================
CREATE TABLE IF NOT EXISTS `backup_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `backup_type` enum('manual','automatic','scheduled') DEFAULT 'manual',
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `status` enum('success','failed','in_progress') DEFAULT 'in_progress',
  `error_message` text,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`backup_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 19. جدول سجلات API (API Logs)
-- ============================================
CREATE TABLE IF NOT EXISTS `api_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `endpoint` varchar(255) NOT NULL,
  `method` varchar(10) NOT NULL,
  `request_data` text,
  `response_data` text,
  `response_code` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `execution_time` decimal(10,4) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_endpoint` (`endpoint`),
  KEY `idx_method` (`method`),
  KEY `idx_response_code` (`response_code`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 20. جدول سجلات الأمان (Security Logs)
-- ============================================
CREATE TABLE IF NOT EXISTS `security_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `event_type` enum('login','logout','failed_login','password_change','permission_change','suspicious_activity') NOT NULL,
  `description` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `severity` enum('low','medium','high','critical') DEFAULT 'low',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_severity` (`severity`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 21. جدول رموز التحقق (Verification Codes)
-- ============================================
CREATE TABLE IF NOT EXISTS `verification_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `code` varchar(10) NOT NULL,
  `type` enum('email_verification','phone_verification','password_reset','two_factor') NOT NULL,
  `status` enum('pending','used','expired') DEFAULT 'pending',
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_code` (`code`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 22. جدول رسائل واتساب الواردة (WhatsApp Incoming)
-- ============================================
CREATE TABLE IF NOT EXISTS `whatsapp_incoming` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_number` varchar(20) NOT NULL,
  `to_number` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `message_id` varchar(100) DEFAULT NULL,
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('received','processed','replied','ignored') DEFAULT 'received',
  `reply_sent` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_from` (`from_number`),
  KEY `idx_status` (`status`),
  KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 23. جدول رسائل واتساب الصادرة (WhatsApp Messages)
-- ============================================
CREATE TABLE IF NOT EXISTS `whatsapp_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to_number` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `template_name` varchar(100) DEFAULT NULL,
  `variables` json DEFAULT NULL,
  `message_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','sent','delivered','read','failed') DEFAULT 'pending',
  `error_message` text,
  `sent_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_to` (`to_number`),
  KEY `idx_status` (`status`),
  KEY `idx_template` (`template_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- إدراج البيانات الافتراضية
-- ============================================

-- مستخدم إداري
INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `role`, `status`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'المدير العام', 'admin@rental.com', 'admin', 'active')
ON DUPLICATE KEY UPDATE `username` = `username`;

-- الإعدادات
INSERT INTO `settings` (`category`, `setting_key`, `setting_value`, `setting_type`) VALUES
('general', 'system_name', 'نظام تأجير السيارات المتقدم', 'string'),
('general', 'company_name', 'شركة تأجير السيارات', 'string'),
('general', 'company_phone', '+970599999999', 'string'),
('financial', 'currency', 'ILS', 'string'),
('financial', 'tax_rate', '17.00', 'number'),
('financial', 'late_fee_per_day', '50.00', 'number')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- القوالب
INSERT INTO `templates` (`name`, `type`, `content`, `language`, `status`) VALUES
('booking_confirmation', 'whatsapp', 'مرحباً {{customer_name}}، تم تأكيد حجزك بنجاح!', 'ar', 'active'),
('payment_reminder', 'whatsapp', 'تذكير بدفع المبلغ المتبقي {{amount}} ₪', 'ar', 'active')
ON DUPLICATE KEY UPDATE `content` = VALUES(`content`);

SELECT 'Database schema created successfully with 23 tables!' as Status;