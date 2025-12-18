-- database/updates/add-notifications.sql
-- إضافة جدول الإشعارات والغرامات

-- جدول الإشعارات
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','danger','rental_ending','rental_late','maintenance','payment') DEFAULT 'info',
  `related_id` int(11) DEFAULT NULL COMMENT 'ID of related rental/car/etc',
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  KEY `type` (`type`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إضافة عمود الغرامات للحجوزات
ALTER TABLE `rentals` 
ADD COLUMN `late_penalty` decimal(10,2) DEFAULT 0.00 AFTER `total_amount`,
ADD COLUMN `notes` text AFTER `late_penalty`;

-- إضافة حقول الصيانة للسيارات
ALTER TABLE `cars`
ADD COLUMN `last_maintenance_date` date DEFAULT NULL AFTER `status`,
ADD COLUMN `last_maintenance_mileage` int(11) DEFAULT 0 AFTER `last_maintenance_date`,
ADD COLUMN `next_maintenance_date` date DEFAULT NULL AFTER `last_maintenance_mileage`,
ADD COLUMN `mileage` int(11) DEFAULT 0 AFTER `next_maintenance_date`;

-- جدول سجل النشاطات
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول الدفعات
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rental_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','credit_card','debit_card','bank_transfer') NOT NULL,
  `payment_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'completed',
  `notes` text,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `rental_id` (`rental_id`),
  KEY `payment_method` (`payment_method`),
  KEY `status` (`status`),
  CONSTRAINT `payments_rental_fk` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إضافة مؤشرات لتحسين الأداء
ALTER TABLE `rentals` ADD INDEX `idx_status_dates` (`status`, `start_date`, `end_date`);
ALTER TABLE `rentals` ADD INDEX `idx_created_at` (`created_at`);
ALTER TABLE `cars` ADD INDEX `idx_status` (`status`);
ALTER TABLE `cars` ADD INDEX `idx_maintenance` (`next_maintenance_date`);

-- إدراج بعض الإشعارات التجريبية
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `created_at`) VALUES
(1, 'مرحباً بك في النظام', 'تم تفعيل نظام الإشعارات بنجاح', 'success', NOW()),
(1, 'تحديث النظام', 'تم إضافة ميزات جديدة للنظام', 'info', NOW());