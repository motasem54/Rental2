-- database/updates/add-settings-tables.sql
-- جداول الإعدادات

-- جدول الإعدادات العامة
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting` (`category`, `setting_key`),
  KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول قوالب واتساب
CREATE TABLE IF NOT EXISTS `whatsapp_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `language` varchar(10) DEFAULT 'ar',
  `content` text NOT NULL,
  `variables` json,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إدراج إعدادات افتراضية
INSERT INTO `system_settings` (`category`, `setting_key`, `setting_value`) VALUES
('general', 'system_name', 'نظام تأجير السيارات المتقدم'),
('general', 'company_name', 'شركة تأجير السيارات'),
('general', 'company_address', 'فلسطين'),
('general', 'company_phone', '+970599999999'),
('general', 'company_email', 'info@rental.com'),
('general', 'default_language', 'ar'),
('general', 'timezone', 'Asia/Gaza'),
('appearance', 'primary_color', '#FF5722'),
('appearance', 'secondary_color', '#121212'),
('appearance', 'dark_mode', '1'),
('appearance', 'rtl_mode', '1'),
('appearance', 'glass_effect', '1'),
('appearance', 'animations', '1'),
('financial', 'currency', 'ILS'),
('financial', 'tax_rate', '17.00'),
('financial', 'deposit_percentage', '40.00'),
('financial', 'late_fee_per_day', '50.00'),
('financial', 'full_insurance_rate', '15.00'),
('financial', 'partial_insurance_rate', '12.00'),
('financial', 'third_party_insurance_rate', '8.00'),
('whatsapp', 'whatsapp_enabled', '1'),
('whatsapp', 'whatsapp_booking_confirmation', '1'),
('whatsapp', 'whatsapp_payment_reminder', '1'),
('whatsapp', 'whatsapp_maintenance_alert', '1')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- إدراج قوالب واتساب افتراضية
INSERT INTO `whatsapp_templates` (`name`, `language`, `content`, `variables`) VALUES
('booking_confirmation', 'ar', 'مرحباً {{customer_name}}،\n\nتم تأكيد حجزك بنجاح!\n\nالسيارة: {{car_name}}\nمن: {{start_date}}\nإلى: {{end_date}}\nالمبلغ: {{amount}} ₪\n\nشكراً لتعاملكم معنا.', '["customer_name", "car_name", "start_date", "end_date", "amount"]'),
('payment_reminder', 'ar', 'عزيزي {{customer_name}}،\n\nنذكرك بدفع المبلغ المتبقي {{amount}} ₪ للحجز رقم {{booking_id}}.\n\nيرجى الدفع قبل {{due_date}}.', '["customer_name", "amount", "booking_id", "due_date"]'),
('late_return', 'ar', 'عزيزي {{customer_name}}،\n\nنأمل منكم إعادة السيارة {{car_name}} في أقرب وقت ممكن.\n\nغرامة التأخير: {{penalty}} ₪\n\nشكراً لتفهمكم.', '["customer_name", "car_name", "penalty"]')
ON DUPLICATE KEY UPDATE content = VALUES(content);

-- إنشاء مجلد للنسخ الاحتياطية
-- mkdir -p ../backups && chmod 755 ../backups