-- --------------------------------------------------------
-- (J'F'* *,1J(J) DF8'E *#,J1 'D3J'1'* 'DE*B/E
-- 'D%5/'1: 2.0.0
-- --------------------------------------------------------

USE `car_rental_advanced`;

-- -0A 'D(J'F'* 'DEH,H/) ('.*J'1J)
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `activity_log`;
TRUNCATE TABLE `rental_logs`;
TRUNCATE TABLE `bookings`;
TRUNCATE TABLE `rentals`;
TRUNCATE TABLE `maintenance`;
TRUNCATE TABLE `insurance`;
TRUNCATE TABLE `cars`;
TRUNCATE TABLE `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- 'DE3*./EJF
-- --------------------------------------------------------

INSERT INTO `users` (`username`, `password`, `email`, `full_name`, `phone`, `role`, `status`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@rentalsys.com', 'E/J1 'DF8'E', '+972599000001', 'admin', 'active'),
('employee1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee@rentalsys.com', '#-E/ E-E/', '+972599000002', 'employee', 'active'),
('customer1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer1@example.com', '.'D/ 'D9DJ', '+972599111001', 'customer', 'active'),
('customer2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer2@example.com', 'E-E/ 'D39/J', '+972599111002', 'customer', 'active'),
('customer3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer3@example.com', '9E1 'D-3F', '+972599111003', 'customer', 'active');

-- CDE) 'DE1H1 DD,EJ9: password

-- --------------------------------------------------------
-- 'D3J'1'*
-- --------------------------------------------------------

INSERT INTO `cars` (`brand`, `model`, `year`, `plate_number`, `color`, `daily_rate`, `weekly_rate`, `monthly_rate`, `fuel_type`, `transmission`, `seats`, `status`, `mileage`) VALUES
('*HJH*'', 'C'E1J', 2023, 'ABC-123', '#(J6', 150.00, 900.00, 3000.00, 'petrol', 'automatic', 5, 'available', 15000),
('GJHF/'J', '%DF*1'', 2023, 'DEF-456', '#3H/', 120.00, 700.00, 2400.00, 'petrol', 'automatic', 5, 'available', 12000),
('CJ'', '3(H1*',', 2022, 'GHI-789', '#21B', 180.00, 1100.00, 3600.00, 'diesel', 'automatic', 5, 'available', 22000),
('FJ3'F', '3F*1'', 2023, 'JKL-012', 'A6J', 130.00, 780.00, 2600.00, 'petrol', 'automatic', 5, 'rented', 8500),
('E13J/3', 'C-Class', 2023, 'MNO-345', '#3H/', 300.00, 1800.00, 6000.00, 'petrol', 'automatic', 5, 'available', 5000),
('BMW', '320i', 2022, 'PQR-678', '#(J6', 280.00, 1680.00, 5600.00, 'petrol', 'automatic', 5, 'available', 18000),
('AH1/', 'AHC3', 2021, 'STU-901', '#-E1', 100.00, 600.00, 2000.00, 'petrol', 'manual', 5, 'maintenance', 45000),
('4JA1HDJG', 'C1H2', 2023, 'VWX-234', '1E'/J', 110.00, 660.00, 2200.00, 'petrol', 'automatic', 5, 'available', 9000),
('E'2/'', '6', 2022, 'YZA-567', '#(J6', 140.00, 840.00, 2800.00, 'petrol', 'automatic', 5, 'available', 16000),
('GHF/'', '#CH1/', 2023, 'BCD-890', '#21B', 160.00, 960.00, 3200.00, 'hybrid', 'automatic', 5, 'reserved', 7000);

-- --------------------------------------------------------
-- 'D-,H2'*
-- --------------------------------------------------------

INSERT INTO `bookings` (`booking_number`, `customer_id`, `car_id`, `start_date`, `end_date`, `days`, `daily_rate`, `subtotal`, `maintenance_cost`, `insurance_type`, `insurance_cost`, `tax_amount`, `total`, `deposit`, `status`) VALUES
('BK-2024-001', 3, 4, '2024-12-01', '2024-12-05', 4, 130.00, 520.00, 60.00, 'partial', 41.60, 98.80, 720.40, 200.00, 'active'),
('BK-2024-002', 4, 10, '2024-12-03', '2024-12-10', 7, 160.00, 1120.00, 105.00, 'full', 179.20, 208.21, 1612.41, 300.00, 'confirmed'),
('BK-2024-003', 5, 1, '2024-12-05', '2024-12-07', 2, 150.00, 300.00, 30.00, 'third_party', 24.00, 53.04, 407.04, 150.00, 'pending');

-- --------------------------------------------------------
-- 'D5J'F)
-- --------------------------------------------------------

INSERT INTO `maintenance` (`car_id`, `maintenance_type`, `description`, `cost`, `maintenance_date`, `next_maintenance_date`, `performed_by`, `status`) VALUES
(7, 'periodic', '5J'F) /H1J) 4'ED) - *:JJ1 2J* HAD'*1', 450.00, '2024-11-20', '2025-02-20', 'H14) 'DFH1 DD3J'1'*', 'completed'),
(1, 'repair', '%5D'- A1'ED #E'EJ)', 280.00, '2024-11-15', NULL, 'H14) 'DE-*1AJF', 'completed'),
(3, 'periodic', 'A-5 /H1J - 20000 CE', 350.00, '2024-12-01', '2025-03-01', 'HC'D) CJ'', 'in_progress');

-- --------------------------------------------------------
-- 'D*#EJF
-- --------------------------------------------------------

INSERT INTO `insurance` (`car_id`, `insurance_company`, `policy_number`, `insurance_type`, `start_date`, `end_date`, `premium_amount`, `coverage_amount`, `status`) VALUES
(1, '41C) 'D*#EJF 'DH7FJ)', 'POL-2024-001', 'full', '2024-01-01', '2024-12-31', 1200.00, 50000.00, 'active'),
(2, '41C) 'D*#EJF 'DH7FJ)', 'POL-2024-002', 'partial', '2024-01-01', '2024-12-31', 900.00, 30000.00, 'active'),
(5, '41C) 'D*#EJF 'D/HDJ)', 'POL-2024-005', 'full', '2024-06-01', '2025-05-31', 2500.00, 100000.00, 'active'),
(6, '41C) 'D*#EJF 'D/HDJ)', 'POL-2024-006', 'full', '2024-06-01', '2025-05-31', 2400.00, 95000.00, 'active');

-- --------------------------------------------------------
-- 3,D 'DF4'7'*
-- --------------------------------------------------------

INSERT INTO `activity_log` (`user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, '*3,JD /.HD', 'B'E E/J1 'DF8'E (*3,JD 'D/.HD', '127.0.0.1', NOW()),
(1, '%6'A) 3J'1)', '*E %6'A) 3J'1) *HJH*' C'E1J 2023', '127.0.0.1', NOW()),
(2, '*3,JD /.HD', 'B'E 'DEH8A #-E/ (*3,JD 'D/.HD', '127.0.0.1', NOW()),
(3, '-,2 3J'1)', 'B'E 'D9EJD .'D/ (-,2 3J'1) FJ3'F 3F*1'', '127.0.0.1', NOW());

-- --------------------------------------------------------
-- 'D%9/'/'*
-- --------------------------------------------------------

INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`) VALUES
('site_name', 'F8'E *#,J1 'D3J'1'* 'DE*B/E', 'text', 'general', ''3E 'DEHB9'),
('currency', 'ILS', 'text', 'financial', ''D9ED) 'DE3*./E)'),
('currency_symbol', 'ª', 'text', 'financial', '1E2 'D9ED)'),
('tax_rate', '17', 'number', 'financial', 'F3() 'D61J() %'),
('insurance_rate', '8', 'number', 'financial', 'F3() 'D*#EJF %'),
('late_fee_percent', '25', 'number', 'financial', 'F3() 13HE 'D*#.J1 %'),
('deposit_percent', '40', 'number', 'financial', 'F3() 'D91(HF %'),
('maintenance_alert_days', '7', 'number', 'maintenance', '#J'E 'D*F(JG DD5J'F)'),
('max_booking_days', '90', 'number', 'booking', '#B5I 9// #J'E DD-,2'),
('min_booking_days', '1', 'number', 'booking', '#BD 9// #J'E DD-,2'),
('whatsapp_enabled', '1', 'boolean', 'notifications', '*A9JD %49'1'* H'*3'('),
('email_enabled', '1', 'boolean', 'notifications', '*A9JD %49'1'* 'D(1J/'),
('backup_enabled', '1', 'boolean', 'system', '*A9JD 'DF3. 'D'-*J'7J 'D*DB'&J');

-- --------------------------------------------------------
-- 'DFG'J)
-- --------------------------------------------------------

-- 916 ED.5 'D(J'F'* 'DEO/.D)
SELECT '*E %/.'D 'D(J'F'* 'D*,1J(J) (F,'-!' AS status;
SELECT COUNT(*) AS total_users FROM users;
SELECT COUNT(*) AS total_cars FROM cars;
SELECT COUNT(*) AS total_bookings FROM bookings;
SELECT COUNT(*) AS total_maintenance FROM maintenance;
SELECT COUNT(*) AS total_insurance FROM insurance;
