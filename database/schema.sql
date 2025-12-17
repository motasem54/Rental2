-- --------------------------------------------------------
-- قاعدة بيانات نظام تأجير السيارات المتقدم
-- الإصدار: 2.0.0
-- تاريخ الإنشاء: 2024
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `car_rental_advanced` 
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `car_rental_advanced`;


-- إضافة جداول جديدة لنظام الصلاحيات والحسابات
CREATE TABLE `security_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `action` VARCHAR(100) NOT NULL,
    `details` TEXT,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    
    INDEX `idx_user_action` (`user_id`, `action`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `verification_codes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `rental_id` INT NOT NULL,
    `action` VARCHAR(50) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `used` BOOLEAN DEFAULT FALSE,
    `used_at` DATETIME,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`rental_id`) REFERENCES `rentals`(`id`) ON DELETE CASCADE,
    
    INDEX `idx_code` (`code`),
    INDEX `idx_rental_action` (`rental_id`, `action`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إضافة حقول جديدة لجدول التأجير
ALTER TABLE `rentals` 
ADD COLUMN `pickup_time` TIME DEFAULT '14:00:00',
ADD COLUMN `return_time` TIME DEFAULT '12:00:00',
ADD COLUMN `actual_return_datetime` DATETIME,
ADD COLUMN `actual_return_km` INT,
ADD COLUMN `late_return_fee` DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN `extra_km_fee` DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN `cleaning_fee` DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN `fuel_fee` DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN `damage_fee` DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN `total_extra_fees` DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN `final_amount` DECIMAL(10,2),
ADD COLUMN `calculation_notes` TEXT,
ADD COLUMN `requires_verification` BOOLEAN DEFAULT FALSE,
ADD COLUMN `verified_by` INT,
ADD COLUMN `verified_at` DATETIME;

-- إضافة حقول للمستخدمين
ALTER TABLE `users`
ADD COLUMN `permissions` JSON,
ADD COLUMN `last_password_change` DATETIME,
ADD COLUMN `failed_login_attempts` INT DEFAULT 0,
ADD COLUMN `account_locked_until` DATETIME;

-- إضافة إعدادات جديدة
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`) VALUES
('late_fee_per_hour', '50', 'number', 'financial', 'رسوم التأخير لكل ساعة'),
('grace_period_minutes', '60', 'number', 'financial', 'فترة السماح بالدقائق'),
('extra_km_rate', '2', 'number', 'financial', 'سعر الكيلومتر الإضافي'),
('min_rental_hours', '3', 'number', 'rental', 'الحد الأدنى لساعات التأجير'),
('require_verification_amount', '5000', 'number', 'security', 'المبلغ الذي يتطلب تحقق'),
('max_failed_logins', '5', 'number', 'security', 'أقصى عدد لمحاولات الدخول الفاشلة'),
('account_lock_minutes', '30', 'number', 'security', 'دقائق قفل الحساب بعد محاولات فاشلة');


-- --------------------------------------------------------
-- جدول المستخدمين
-- --------------------------------------------------------
-- بدل: bookings
CREATE TABLE `rentals` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `rental_number` VARCHAR(50) UNIQUE NOT NULL,  -- بدل: booking_number
    `customer_id` INT NOT NULL,
    `car_id` INT NOT NULL,
    `rental_type` ENUM('daily', 'weekly', 'monthly', 'long_term') DEFAULT 'daily',  -- نوع التأجير
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `rental_days` INT NOT NULL,  -- بدل: days
    `daily_rate` DECIMAL(10,2) NOT NULL,
    `total_rental_amount` DECIMAL(10,2) NOT NULL,  -- بدل: total
    `deposit_amount` DECIMAL(10,2) NOT NULL,  -- بدل: deposit
    `rental_status` ENUM('pending', 'active', 'completed', 'cancelled') DEFAULT 'pending',  -- بدل: status
    `contract_signed` BOOLEAN DEFAULT FALSE,  -- عقد موقّع
    `km_allowed` INT,  -- الكيلومترات المسموح بها
    `extra_km_rate` DECIMAL(10,2),  -- سعر الكيلومتر الإضافي
    `pickup_km` INT,  -- عداد الاستلام
    `return_km` INT,  -- عداد الإرجاع
    `pickup_condition` TEXT,
    `return_condition` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`car_id`) REFERENCES `cars`(`id`) ON DELETE CASCADE
);

-- بدل: booking_logs
CREATE TABLE `rental_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `rental_id` INT NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `created_by` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`rental_id`) REFERENCES `rentals`(`id`) ON DELETE CASCADE
);

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20),
  `address` TEXT,
  `driver_license` VARCHAR(50),
  `license_expiry` DATE,
  `role` ENUM('admin', 'customer', 'employee') DEFAULT 'customer',
  `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
  `profile_image` VARCHAR(255),
  `last_login` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول السيارات
-- --------------------------------------------------------

CREATE TABLE `cars` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `brand` VARCHAR(100) NOT NULL,
  `model` VARCHAR(100) NOT NULL,
  `year` INT NOT NULL,
  `plate_number` VARCHAR(20) UNIQUE NOT NULL,
  `color` VARCHAR(50),
  `daily_rate` DECIMAL(10,2) NOT NULL,
  `weekly_rate` DECIMAL(10,2),
  `monthly_rate` DECIMAL(10,2),
  `maintenance_cost_per_day` DECIMAL(10,2) DEFAULT 15.00,
  `insurance_type` ENUM('full', 'partial', 'third_party') DEFAULT 'third_party',
  `insurance_daily_cost` DECIMAL(10,2) DEFAULT 0.00,
  `fuel_type` ENUM('petrol', 'diesel', 'electric', 'hybrid') DEFAULT 'petrol',
  `transmission` ENUM('automatic', 'manual') DEFAULT 'automatic',
  `seats` INT DEFAULT 5,
  `doors` INT DEFAULT 4,
  `features` TEXT,
  `image` VARCHAR(255),
  `status` ENUM('available', 'rented', 'maintenance', 'reserved', 'out_of_service') DEFAULT 'available',
  `mileage` INT DEFAULT 0,
  `last_maintenance_date` DATE,
  `next_maintenance_date` DATE,
  `maintenance_interval_days` INT DEFAULT 90,
  `purchase_date` DATE,
  `purchase_price` DECIMAL(15,2),
  `current_value` DECIMAL(15,2),
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX `idx_brand` (`brand`),
  INDEX `idx_status` (`status`),
  INDEX `idx_plate` (`plate_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول الحجوزات
-- --------------------------------------------------------

CREATE TABLE `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_number` VARCHAR(50) UNIQUE NOT NULL,
  `customer_id` INT NOT NULL,
  `car_id` INT NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `days` INT NOT NULL,
  `pickup_location` VARCHAR(255),
  `return_location` VARCHAR(255),
  `daily_rate` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `maintenance_cost` DECIMAL(10,2) NOT NULL,
  `insurance_type` ENUM('full', 'partial', 'third_party') NOT NULL,
  `insurance_cost` DECIMAL(10,2) NOT NULL,
  `tax_rate` DECIMAL(5,2) DEFAULT 17.00,
  `tax` DECIMAL(10,2) NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  `deposit` DECIMAL(10,2) NOT NULL,
  `paid_amount` DECIMAL(10,2) DEFAULT 0.00,
  `payment_status` ENUM('pending', 'partial', 'paid', 'overdue') DEFAULT 'pending',
  `status` ENUM('pending', 'confirmed', 'active', 'completed', 'cancelled', 'no_show') DEFAULT 'pending',
  `cancellation_reason` TEXT,
  `cancellation_fee` DECIMAL(10,2) DEFAULT 0.00,
  `driver_name` VARCHAR(100),
  `driver_license` VARCHAR(50),
  `driver_age` INT,
  `additional_drivers` TEXT,
  `special_requests` TEXT,
  `pickup_odometer` INT,
  `return_odometer` INT,
  `pickup_condition` TEXT,
  `return_condition` TEXT,
  `pickup_images` TEXT,
  `return_images` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`car_id`) REFERENCES `cars`(`id`) ON DELETE CASCADE,
  
  INDEX `idx_booking_number` (`booking_number`),
  INDEX `idx_customer` (`customer_id`),
  INDEX `idx_car` (`car_id`),
  INDEX `idx_dates` (`start_date`, `end_date`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول المدفوعات
-- --------------------------------------------------------

CREATE TABLE `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT NOT NULL,
  `payment_method` ENUM('cash', 'credit_card', 'bank_transfer', 'check', 'mobile_payment') NOT NULL,
  `payment_type` ENUM('deposit', 'full_payment', 'partial_payment', 'refund', 'cancellation_fee') NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(10) DEFAULT 'ILS',
  `transaction_id` VARCHAR(100),
  `receipt_number` VARCHAR(100),
  `payment_date` DATE NOT NULL,
  `due_date` DATE,
  `status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
  
  INDEX `idx_booking` (`booking_id`),
  INDEX `idx_payment_date` (`payment_date`),
  INDEX `idx_transaction` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول الصيانة
-- --------------------------------------------------------

CREATE TABLE `maintenance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `car_id` INT NOT NULL,
  `maintenance_type` ENUM('routine', 'repair', 'accident', 'recall') NOT NULL,
  `description` TEXT NOT NULL,
  `cost` DECIMAL(10,2) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE,
  `vendor` VARCHAR(100),
  `invoice_number` VARCHAR(100),
  `parts_replaced` TEXT,
  `labor_hours` DECIMAL(5,2),
  `technician` VARCHAR(100),
  `status` ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`car_id`) REFERENCES `cars`(`id`) ON DELETE CASCADE,
  
  INDEX `idx_car` (`car_id`),
  INDEX `idx_dates` (`start_date`, `end_date`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول التأمين
-- --------------------------------------------------------

CREATE TABLE `insurance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `car_id` INT NOT NULL,
  `insurance_company` VARCHAR(100) NOT NULL,
  `policy_number` VARCHAR(100) NOT NULL,
  `coverage_type` ENUM('comprehensive', 'third_party', 'collision', 'theft') NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `premium_amount` DECIMAL(10,2) NOT NULL,
  `deductible` DECIMAL(10,2),
  `coverage_limit` DECIMAL(15,2),
  `agent_name` VARCHAR(100),
  `agent_phone` VARCHAR(20),
  `document_path` VARCHAR(255),
  `status` ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
  `renewal_reminder_sent` BOOLEAN DEFAULT FALSE,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`car_id`) REFERENCES `cars`(`id`) ON DELETE CASCADE,
  
  UNIQUE KEY `unique_policy` (`policy_number`, `insurance_company`),
  INDEX `idx_car` (`car_id`),
  INDEX `idx_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول مطالبات التأمين
-- --------------------------------------------------------

CREATE TABLE `insurance_claims` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `insurance_id` INT NOT NULL,
  `booking_id` INT,
  `claim_number` VARCHAR(100) UNIQUE NOT NULL,
  `incident_date` DATE NOT NULL,
  `report_date` DATE NOT NULL,
  `description` TEXT NOT NULL,
  `damage_description` TEXT,
  `estimated_cost` DECIMAL(10,2),
  `approved_amount` DECIMAL(10,2),
  `status` ENUM('reported', 'under_review', 'approved', 'rejected', 'paid') DEFAULT 'reported',
  `adjuster_name` VARCHAR(100),
  `adjuster_contact` VARCHAR(100),
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`insurance_id`) REFERENCES `insurance`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE SET NULL,
  
  INDEX `idx_insurance` (`insurance_id`),
  INDEX `idx_claim_number` (`claim_number`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول رسائل واتساب
-- --------------------------------------------------------

CREATE TABLE `whatsapp_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `template_name` VARCHAR(100),
  `phone_number` VARCHAR(20) NOT NULL,
  `customer_id` INT,
  `message_type` ENUM('booking', 'reminder', 'payment', 'maintenance', 'promotional', 'custom') NOT NULL,
  `message_content` TEXT,
  `message_id` VARCHAR(255),
  `status` ENUM('pending', 'sent', 'delivered', 'read', 'failed') DEFAULT 'pending',
  `error_message` TEXT,
  `sent_at` DATETIME,
  `delivered_at` DATETIME,
  `read_at` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  
  INDEX `idx_phone` (`phone_number`),
  INDEX `idx_customer` (`customer_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_message_id` (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول الرسائل الواردة
-- --------------------------------------------------------

CREATE TABLE `whatsapp_incoming` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `phone_number` VARCHAR(20) NOT NULL,
  `message_type` VARCHAR(50) NOT NULL,
  `message_id` VARCHAR(255) NOT NULL,
  `message_data` JSON,
  `processed` BOOLEAN DEFAULT FALSE,
  `response_sent` BOOLEAN DEFAULT FALSE,
  `received_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX `idx_phone` (`phone_number`),
  INDEX `idx_received` (`received_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول سجل النشاطات
-- --------------------------------------------------------

CREATE TABLE `activity_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `action` VARCHAR(100) NOT NULL,
  `module` VARCHAR(50),
  `record_id` INT,
  `details` TEXT,
  `ip_address` VARCHAR(45),
  `user_agent` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  
  INDEX `idx_user` (`user_id`),
  INDEX `idx_module` (`module`),
  INDEX `idx_action` (`action`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول الإشعارات
-- --------------------------------------------------------

CREATE TABLE `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info', 'success', 'warning', 'error', 'booking', 'payment', 'maintenance') DEFAULT 'info',
  `is_read` BOOLEAN DEFAULT FALSE,
  `action_url` VARCHAR(255),
  `sent_via` ENUM('system', 'email', 'whatsapp', 'sms') DEFAULT 'system',
  `scheduled_at` DATETIME,
  `sent_at` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  
  INDEX `idx_user` (`user_id`),
  INDEX `idx_read` (`is_read`),
  INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول التقارير
-- --------------------------------------------------------

CREATE TABLE `reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `report_type` VARCHAR(50) NOT NULL,
  `report_name` VARCHAR(255) NOT NULL,
  `parameters` JSON,
  `generated_by` INT,
  `file_path` VARCHAR(255),
  `file_size` INT,
  `download_count` INT DEFAULT 0,
  `status` ENUM('generating', 'completed', 'failed') DEFAULT 'generating',
  `error_message` TEXT,
  `generated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`generated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  
  INDEX `idx_type` (`report_type`),
  INDEX `idx_created` (`generated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول الإعدادات
-- --------------------------------------------------------

CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) UNIQUE NOT NULL,
  `setting_value` TEXT,
  `setting_type` ENUM('string', 'number', 'boolean', 'json', 'array') DEFAULT 'string',
  `category` VARCHAR(50),
  `description` TEXT,
  `is_public` BOOLEAN DEFAULT FALSE,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول سجلات النسخ الاحتياطي
-- --------------------------------------------------------

CREATE TABLE `backup_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `backup_type` ENUM('full', 'database', 'files') NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `file_size` BIGINT,
  `status` ENUM('success', 'failed') NOT NULL,
  `error_message` TEXT,
  `created_by` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  
  INDEX `idx_type` (`backup_type`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول سجلات API
-- --------------------------------------------------------

CREATE TABLE `api_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `api_key` VARCHAR(255),
  `endpoint` VARCHAR(255) NOT NULL,
  `method` VARCHAR(10) NOT NULL,
  `request_data` JSON,
  `response_data` JSON,
  `status_code` INT,
  `response_time` FLOAT,
  `ip_address` VARCHAR(45),
  `user_agent` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX `idx_endpoint` (`endpoint`),
  INDEX `idx_method` (`method`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول القوالب
-- --------------------------------------------------------

CREATE TABLE `templates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `template_type` ENUM('whatsapp', 'email', 'sms', 'invoice', 'contract') NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(255),
  `content` TEXT NOT NULL,
  `variables` JSON,
  `language` VARCHAR(10) DEFAULT 'ar',
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  UNIQUE KEY `unique_template` (`template_type`, `name`, `language`),
  INDEX `idx_type` (`template_type`),
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول الرسوم الإضافية
-- --------------------------------------------------------

CREATE TABLE `extra_charges` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT NOT NULL,
  `charge_type` ENUM('late_return', 'extra_mileage', 'cleaning', 'fuel', 'damage', 'other') NOT NULL,
  `description` TEXT NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `quantity` INT DEFAULT 1,
  `total` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pending', 'approved', 'paid', 'cancelled') DEFAULT 'pending',
  `approved_by` INT,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  
  INDEX `idx_booking` (`booking_id`),
  INDEX `idx_type` (`charge_type`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول التقارير المالية
-- --------------------------------------------------------

CREATE TABLE `financial_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `report_period` ENUM('daily', 'weekly', 'monthly', 'yearly') NOT NULL,
  `period_start` DATE NOT NULL,
  `period_end` DATE NOT NULL,
  `total_bookings` INT DEFAULT 0,
  `total_revenue` DECIMAL(15,2) DEFAULT 0.00,
  `total_expenses` DECIMAL(15,2) DEFAULT 0.00,
  `total_profit` DECIMAL(15,2) DEFAULT 0.00,
  `average_daily_revenue` DECIMAL(10,2) DEFAULT 0.00,
  `most_popular_car` INT,
  `most_active_customer` INT,
  `data` JSON,
  `generated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX `idx_period` (`period_start`, `period_end`),
  INDEX `idx_report_period` (`report_period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- إضافة البيانات الأولية
-- --------------------------------------------------------

-- إضافة مستخدم مدير
INSERT INTO `users` (`username`, `password`, `email`, `full_name`, `phone`, `role`, `status`) VALUES
('admin', '$2y$10$YourHashedPasswordHere', 'admin@carrental.com', 'مدير النظام', '0599123456', 'admin', 'active');

-- إضافة سيارات عينة
INSERT INTO `cars` (`brand`, `model`, `year`, `plate_number`, `color`, `daily_rate`, `weekly_rate`, `monthly_rate`, `fuel_type`, `transmission`, `seats`, `doors`, `status`) VALUES
('تويوتا', 'كامري', 2023, 'جدة 1234 أب', 'أسود', 200.00, 1200.00, 4500.00, 'petrol', 'automatic', 5, 4, 'available'),
('هيونداي', 'سوناتا', 2022, 'جدة 5678 أب', 'أبيض', 180.00, 1100.00, 4000.00, 'petrol', 'automatic', 5, 4, 'available'),
('كيا', 'سبورتاج', 2023, 'جدة 9012 أب', 'رمادي', 220.00, 1300.00, 5000.00, 'diesel', 'automatic', 5, 4, 'available'),
('مرسيدس', 'فئة C', 2023, 'جدة 3456 أب', 'فضي', 350.00, 2100.00, 8000.00, 'petrol', 'automatic', 5, 4, 'rented'),
('بي إم دبليو', 'الفئة الثالثة', 2022, 'جدة 7890 أب', 'أزرق', 400.00, 2400.00, 9000.00, 'petrol', 'automatic', 5, 4, 'maintenance');

-- إضافة إعدادات النظام
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`) VALUES
('system_name', 'نظام تأجير السيارات المتقدم', 'string', 'general', 'اسم النظام'),
('currency', 'ILS', 'string', 'financial', 'العملة الافتراضية'),
('tax_rate', '17', 'number', 'financial', 'معدل الضريبة'),
('deposit_percentage', '40', 'number', 'financial', 'نسبة الدفعة المقدمة'),
('late_fee_per_day', '50', 'number', 'financial', 'رسوم التأخير لكل يوم'),
('maintenance_cost_per_day', '15', 'number', 'financial', 'تكلفة الصيانة اليومية'),
('whatsapp_enabled', 'true', 'boolean', 'whatsapp', 'تفعيل واتساب'),
('whatsapp_token', '', 'string', 'whatsapp', 'توكن واتساب'),
('whatsapp_phone_id', '', 'string', 'whatsapp', 'رقم هاتف واتساب'),
('theme_primary_color', '#FF5722', 'string', 'appearance', 'اللون الرئيسي'),
('theme_secondary_color', '#121212', 'string', 'appearance', 'اللون الثانوي'),
('logo_path', 'assets/images/logo.png', 'string', 'appearance', 'مسار الشعار'),
('auto_backup_enabled', 'true', 'boolean', 'system', 'تفعيل النسخ الاحتياطي التلقائي'),
('backup_interval_days', '7', 'number', 'system', 'فترة النسخ الاحتياطي بالأيام'),
('max_login_attempts', '5', 'number', 'security', 'أقصى عدد لمحاولات الدخول'),
('session_timeout_minutes', '30', 'number', 'security', 'مهلة الجلسة بالدقائق');

-- إضافة قوالب واتساب
INSERT INTO `templates` (`template_type`, `name`, `subject`, `content`, `variables`, `language`) VALUES
('whatsapp', 'booking_confirmation', 'تأكيد الحجز', 'مرحباً {{customer_name}}،\nتم تأكيد حجزك رقم {{booking_id}}\nالسيارة: {{car_name}}\nالفترة: {{start_date}} إلى {{end_date}}\nالمبلغ الإجمالي: {{total_amount}} ₪\n\nشكراً لاختياركم خدماتنا!', '["customer_name", "booking_id", "car_name", "start_date", "end_date", "total_amount"]', 'ar'),
('whatsapp', 'payment_reminder', 'تذكير بالدفع', 'عزيزي {{customer_name}}،\nتذكير بدفع مبلغ {{amount}} ₪ المستحق بتاريخ {{due_date}}\nرقم الحجز: {{booking_id}}\n\nيرجى التسديد في أقرب وقت ممكن.', '["customer_name", "amount", "due_date", "booking_id"]', 'ar'),
('whatsapp', 'maintenance_alert', 'تنبيه صيانة', 'عزيزي {{customer_name}}،\nالسيارة {{car_name}} تحتاج لصيانة بتاريخ {{maintenance_date}}\nنوع الصيانة: {{maintenance_type}}\n\nيرجى حجز موعد للصيانة.', '["customer_name", "car_name", "maintenance_date", "maintenance_type"]', 'ar'),
('whatsapp', 'booking_completion', 'اكتمال الحجز', 'شكراً لك {{customer_name}}،\nتم إكمال حجزك رقم {{booking_id}} بنجاح\nنأمل أن تكون تجربتك معنا ممتازة\n\nنتطلع لخدمتك مرة أخرى!', '["customer_name", "booking_id"]', 'ar');

-- إضافة محتوى تقرير مالي شهري عينة
INSERT INTO `financial_reports` (`report_period`, `period_start`, `period_end`, `total_bookings`, `total_revenue`, `total_expenses`, `total_profit`) VALUES
('monthly', '2024-01-01', '2024-01-31', 45, 85000.00, 25000.00, 60000.00),
('monthly', '2024-02-01', '2024-02-29', 52, 92000.00, 28000.00, 64000.00);

-- --------------------------------------------------------
-- إنشاء الأحداث
-- --------------------------------------------------------

-- حدث للتحقق من تواريخ الصيانة
DELIMITER $$
CREATE EVENT `check_maintenance_due`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    -- تحديث حالة السيارات التي تحتاج صيانة
    UPDATE cars 
    SET status = 'maintenance'
    WHERE next_maintenance_date <= CURDATE()
    AND status NOT IN ('maintenance', 'rented');
    
    -- إرسال إشعارات للسيارات التي تحتاج صيانة خلال 7 أيام
    INSERT INTO notifications (user_id, title, message, type, sent_via)
    SELECT u.id,
           'تنبيه صيانة',
           CONCAT('السيارة ', c.brand, ' ', c.model, ' تحتاج صيانة بتاريخ ', c.next_maintenance_date),
           'maintenance',
           'system'
    FROM cars c
    JOIN users u ON u.role = 'admin'
    WHERE c.next_maintenance_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    AND c.status NOT IN ('maintenance');
END$$
DELIMITER ;

-- حدث للتحقق من تأجير انتهى
DELIMITER $$
CREATE EVENT `check_expired_bookings`
ON SCHEDULE EVERY 1 HOUR
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    -- تحديث الحجوزات المنتهية
    UPDATE bookings 
    SET status = 'completed'
    WHERE end_date < CURDATE()
    AND status = 'active';
    
    -- تحديث حالة السيارات المرتجعة
    UPDATE cars c
    JOIN bookings b ON c.id = b.car_id
    SET c.status = 'available'
    WHERE b.end_date < CURDATE()
    AND b.status = 'completed'
    AND c.status = 'rented';
    
    -- إضافة رسوم التأخير للحجوزات المتأخرة
    INSERT INTO extra_charges (booking_id, charge_type, description, amount, quantity, total, status)
    SELECT b.id,
           'late_return',
           CONCAT('رسوم تأخير إرجاع السيارة: ', DATEDIFF(CURDATE(), b.end_date), ' يوم'),
           50.00,
           DATEDIFF(CURDATE(), b.end_date),
           50.00 * DATEDIFF(CURDATE(), b.end_date),
           'pending'
    FROM bookings b
    WHERE b.end_date < CURDATE()
    AND b.status = 'active'
    AND NOT EXISTS (
        SELECT 1 FROM extra_charges ec 
        WHERE ec.booking_id = b.id 
        AND ec.charge_type = 'late_return'
        AND ec.status != 'cancelled'
    );
END$$
DELIMITER ;

-- --------------------------------------------------------
-- إنشاء الإجراءات المخزنة
-- --------------------------------------------------------

-- إجراء لحساب إيرادات الشهر
DELIMITER $$
CREATE PROCEDURE `calculate_monthly_revenue`(IN month_year DATE)
BEGIN
    DECLARE start_date DATE;
    DECLARE end_date DATE;
    
    SET start_date = DATE_FORMAT(month_year, '%Y-%m-01');
    SET end_date = LAST_DAY(month_year);
    
    SELECT 
        COUNT(*) as total_bookings,
        SUM(total) as total_revenue,
        SUM(paid_amount) as total_paid,
        AVG(total) as average_booking_value,
        MIN(total) as min_booking_value,
        MAX(total) as max_booking_value
    FROM bookings
    WHERE DATE(created_at) BETWEEN start_date AND end_date
    AND status IN ('completed', 'active');
END$$
DELIMITER ;

-- إجراء لحجز سيارة
DELIMITER $$
CREATE PROCEDURE `create_booking_with_validation`(
    IN p_customer_id INT,
    IN p_car_id INT,
    IN p_start_date DATE,
    IN p_end_date DATE,
    IN p_insurance_type ENUM('full', 'partial', 'third_party'),
    OUT p_booking_id INT,
    OUT p_status VARCHAR(50),
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE v_car_status VARCHAR(50);
    DECLARE v_days INT;
    DECLARE v_daily_rate DECIMAL(10,2);
    DECLARE v_total DECIMAL(10,2);
    DECLARE v_deposit DECIMAL(10,2);
    DECLARE v_available BOOLEAN DEFAULT TRUE;
    
    -- التحقق من توفر السيارة
    SELECT status INTO v_car_status FROM cars WHERE id = p_car_id;
    
    IF v_car_status != 'available' THEN
        SET p_status = 'failed';
        SET p_message = 'السيارة غير متاحة للإيجار';
        SET v_available = FALSE;
    END IF;
    
    -- التحقق من تعارض التواريخ
    IF v_available THEN
        SELECT COUNT(*) INTO @conflict_count
        FROM bookings
        WHERE car_id = p_car_id
        AND status NOT IN ('cancelled', 'completed')
        AND (
            (p_start_date BETWEEN start_date AND end_date) OR
            (p_end_date BETWEEN start_date AND end_date) OR
            (start_date BETWEEN p_start_date AND p_end_date)
        );
        
        IF @conflict_count > 0 THEN
            SET p_status = 'failed';
            SET p_message = 'السيارة محجوزة في الفترة المحددة';
            SET v_available = FALSE;
        END IF;
    END IF;
    
    -- إنشاء الحجز إذا كانت السيارة متاحة
    IF v_available THEN
        -- حساب عدد الأيام
        SET v_days = DATEDIFF(p_end_date, p_start_date) + 1;
        
        -- الحصول على السعر اليومي
        SELECT daily_rate INTO v_daily_rate FROM cars WHERE id = p_car_id;
        
        -- حساب الإجمالي
        SET v_total = v_daily_rate * v_days;
        SET v_deposit = v_total * 0.4; -- 40% دفعة مقدمة
        
        -- إدخال الحجز
        INSERT INTO bookings (
            booking_number, customer_id, car_id, start_date, end_date, days,
            daily_rate, subtotal, total, deposit, insurance_type, status
        ) VALUES (
            CONCAT('BOOK-', DATE_FORMAT(NOW(), '%Y%m%d-'), LPAD(FLOOR(RAND() * 10000), 4, '0')),
            p_customer_id, p_car_id, p_start_date, p_end_date, v_days,
            v_daily_rate, v_total, v_total, v_deposit, p_insurance_type, 'pending'
        );
        
        SET p_booking_id = LAST_INSERT_ID();
        SET p_status = 'success';
        SET p_message = 'تم إنشاء الحجز بنجاح';
        
        -- تحديث حالة السيارة
        UPDATE cars SET status = 'reserved' WHERE id = p_car_id;
    END IF;
END$$
DELIMITER ;

-- --------------------------------------------------------
-- إنشاء المشغلات (Triggers)
-- --------------------------------------------------------

-- مشغل لتسجيل نشاط تحديث الحجز
DELIMITER $$
CREATE TRIGGER `log_booking_update`
AFTER UPDATE ON `bookings`
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO activity_log (user_id, action, module, record_id, details)
        VALUES (
            NEW.customer_id,
            CONCAT('تحديث حالة الحجز من ', OLD.status, ' إلى ', NEW.status),
            'bookings',
            NEW.id,
            CONCAT('تم تحديث حالة الحجز رقم ', NEW.booking_number)
        );
    END IF;
END$$
DELIMITER ;

-- مشغل لتسجيل إنشاء حجز جديد
DELIMITER $$
CREATE TRIGGER `log_new_booking`
AFTER INSERT ON `bookings`
FOR EACH ROW
BEGIN
    INSERT INTO activity_log (user_id, action, module, record_id, details)
    VALUES (
        NEW.customer_id,
        'إنشاء حجز جديد',
        'bookings',
        NEW.id,
        CONCAT('تم إنشاء حجز جديد رقم ', NEW.booking_number, ' بقيمة ', NEW.total, ' ₪')
    );
END$$
DELIMITER ;

-- مشغل لتسجيل تحديث حالة السيارة
DELIMITER $$
CREATE TRIGGER `log_car_status_change`
AFTER UPDATE ON `cars`
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO activity_log (user_id, action, module, record_id, details)
        VALUES (
            1, -- User ID 1 هو المدير
            CONCAT('تغيير حالة السيارة من ', OLD.status, ' إلى ', NEW.status),
            'cars',
            NEW.id,
            CONCAT('تم تغيير حالة السيارة ', NEW.brand, ' ', NEW.model, ' (', NEW.plate_number, ')')
        );
    END IF;
END$$
DELIMITER ;

-- --------------------------------------------------------
-- إنشاء الفهارس الإضافية لتحسين الأداء
-- --------------------------------------------------------

CREATE INDEX `idx_bookings_dates_status` ON `bookings` (`start_date`, `end_date`, `status`);
CREATE INDEX `idx_cars_status_price` ON `cars` (`status`, `daily_rate`);
CREATE INDEX `idx_payments_booking_status` ON `payments` (`booking_id`, `status`);
CREATE INDEX `idx_users_role_status` ON `users` (`role`, `status`);
CREATE INDEX `idx_activity_user_date` ON `activity_log` (`user_id`, `created_at`);
CREATE INDEX `idx_notifications_user_read` ON `notifications` (`user_id`, `is_read`);

-- --------------------------------------------------------
-- إنشاء المستخدم والصلاحيات
-- --------------------------------------------------------

CREATE USER IF NOT EXISTS 'carrental_user'@'localhost' IDENTIFIED BY 'SecurePass123!';
GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE ON `car_rental_advanced`.* TO 'carrental_user'@'localhost';
FLUSH PRIVILEGES;