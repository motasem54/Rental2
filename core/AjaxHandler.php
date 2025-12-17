<?php
// core/AjaxHandler.php

class AjaxHandler {
    private $db;
    private $auth;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->auth = new Auth();
    }
    
    public function handleRequest() {
        // Check if it's an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            
            $action = $_POST['action'] ?? $_GET['action'] ?? '';
            
            switch ($action) {
                case 'login':
                    return $this->handleLogin();
                case 'register':
                    return $this->handleRegister();
                case 'add_car':
                    return $this->addCar();
                case 'update_car':
                    return $this->updateCar();
                case 'delete_car':
                    return $this->deleteCar();
                case 'create_booking':
                    return $this->createBooking();
                case 'send_whatsapp':
                    return $this->sendWhatsApp();
                case 'get_stats':
                    return $this->getStats();
                default:
                    return $this->jsonResponse(false, 'Action not found');
            }
        }
        
        return $this->jsonResponse(false, 'Not an AJAX request');
    }
    
    private function handleLogin() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            return $this->jsonResponse(false, 'جميع الحقول مطلوبة');
        }
        
        $result = $this->auth->login($username, $password);
        
        if ($result['success']) {
            return $this->jsonResponse(true, 'تم تسجيل الدخول بنجاح', [
                'redirect' => 'dashboard.php'
            ]);
        }
        
        return $this->jsonResponse(false, $result['message']);
    }
    
    private function addCar() {
        // Check if user is admin
        if (!$this->auth->isAdmin()) {
            return $this->jsonResponse(false, 'غير مصرح بهذا الإجراء');
        }
        
        $data = [
            'brand' => $_POST['brand'] ?? '',
            'model' => $_POST['model'] ?? '',
            'year' => $_POST['year'] ?? '',
            'plate_number' => $_POST['plate_number'] ?? '',
            'color' => $_POST['color'] ?? '',
            'daily_rate' => $_POST['daily_rate'] ?? 0,
            'maintenance_cost_per_day' => $_POST['maintenance_cost_per_day'] ?? MAINTENANCE_COST_PER_DAY,
            'insurance_type' => $_POST['insurance_type'] ?? 'third_party',
            'insurance_daily_cost' => $_POST['insurance_daily_cost'] ?? 0,
            'status' => 'available',
            'features' => $_POST['features'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $uploadResult = $this->uploadImage($_FILES['image'], 'cars');
            if ($uploadResult['success']) {
                $data['image'] = $uploadResult['file_path'];
            }
        }
        
        $result = Database::getInstance()->ajaxInsert('cars', $data);
        
        if ($result['success']) {
            // Log activity
            $this->logActivity('إضافة سيارة', 'تمت إضافة سيارة جديدة: ' . $data['brand'] . ' ' . $data['model']);
            
            // Send notification to admin
            $this->sendNotification('سيارة جديدة', 'تمت إضافة سيارة جديدة إلى النظام');
        }
        
        return $result;
    }
    
    private function createBooking() {
        if (!$this->auth->isLoggedIn()) {
            return $this->jsonResponse(false, 'يجب تسجيل الدخول أولاً');
        }
        
        $customer_id = $_SESSION['user_id'];
        $car_id = $_POST['car_id'] ?? 0;
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $insurance_type = $_POST['insurance_type'] ?? 'third_party';
        
        // Calculate total price
        $car = $this->getCarDetails($car_id);
        $days = $this->calculateDays($start_date, $end_date);
        
        $daily_rate = $car['daily_rate'];
        $insurance_cost = $car['insurance_daily_cost'];
        $maintenance_cost = $car['maintenance_cost_per_day'];
        
        $subtotal = ($daily_rate + $maintenance_cost) * $days;
        $insurance_total = $insurance_cost * $days;
        $tax = ($subtotal + $insurance_total) * (TAX_RATE / 100);
        
        $total = $subtotal + $insurance_total + $tax;
        $deposit = $total * (DEPOSIT_PERCENT / 100);
        
        $bookingData = [
            'customer_id' => $customer_id,
            'car_id' => $car_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'days' => $days,
            'daily_rate' => $daily_rate,
            'subtotal' => $subtotal,
            'insurance_type' => $insurance_type,
            'insurance_cost' => $insurance_total,
            'maintenance_cost' => $maintenance_cost * $days,
            'tax' => $tax,
            'total' => $total,
            'deposit' => $deposit,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $result = Database::getInstance()->ajaxInsert('bookings', $bookingData);
        
        if ($result['success']) {
            // Update car status
            $this->updateCarStatus($car_id, 'reserved');
            
            // Send WhatsApp confirmation
            $whatsapp = new WhatsAppAPI();
            $customer = $this->getCustomerDetails($customer_id);
            
            $bookingDetails = [
                'booking_id' => $result['id'],
                'car_name' => $car['brand'] . ' ' . $car['model'],
                'total_price' => $total . ' ₪',
                'start_date' => $start_date,
                'end_date' => $end_date
            ];
            
            $whatsapp->sendBookingConfirmation($customer['phone'], $bookingDetails);
            
            // Log activity
            $this->logActivity('حجز جديد', 'تم إنشاء حجز جديد للسيارة: ' . $car['brand']);
        }
        
        return $result;
    }
    
    private function sendWhatsApp() {
        $phone = $_POST['phone'] ?? '';
        $message = $_POST['message'] ?? '';
        $template = $_POST['template'] ?? '';
        
        if (empty($phone)) {
            return $this->jsonResponse(false, 'رقم الهاتف مطلوب');
        }
        
        $whatsapp = new WhatsAppAPI();
        
        if (!empty($template)) {
            $result = $whatsapp->sendTemplateMessage($phone, $template, $_POST['parameters'] ?? []);
        } else {
            $result = $whatsapp->sendCustomMessage($phone, $message);
        }
        
        if ($result['success']) {
            // Save message to database
            $this->saveWhatsAppMessage($phone, $message, $template, $result['message_id']);
            
            return $this->jsonResponse(true, 'تم إرسال الرسالة بنجاح');
        }
        
        return $this->jsonResponse(false, 'فشل إرسال الرسالة: ' . $result['message']);
    }
    
    private function getStats() {
        $stats = [];
        
        // Total cars
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM cars WHERE status = 'available'");
        $stats['total_cars'] = $stmt->fetch()['count'];
        
        // Total customers
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
        $stats['total_customers'] = $stmt->fetch()['count'];
        
        // Active rentals
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'active'");
        $stats['active_rentals'] = $stmt->fetch()['count'];
        
        // Daily income
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT SUM(total) as income FROM bookings WHERE DATE(created_at) = ? AND status = 'active'");
        $stmt->execute([$today]);
        $stats['daily_income'] = $stmt->fetch()['income'] ?? 0;
        
        return $this->jsonResponse(true, 'تم تحميل الإحصائيات', $stats);
    }
    
    private function uploadImage($file, $folder) {
        $targetDir = UPLOAD_PATH . $folder . '/';
        
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileName = time() . '_' . basename($file['name']);
        $targetFile = $targetDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        
        // Check if image file is a actual image
        $check = getimagesize($file['tmp_name']);
        if ($check === false) {
            return ['success' => false, 'message' => 'الملف ليس صورة'];
        }
        
        // Check file size (5MB max)
        if ($file['size'] > 5000000) {
            return ['success' => false, 'message' => 'حجم الصورة كبير جداً'];
        }
        
        // Allow certain file formats
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            return ['success' => false, 'message' => 'نوع الملف غير مسموح'];
        }
        
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return ['success' => true, 'file_path' => $folder . '/' . $fileName];
        }
        
        return ['success' => false, 'message' => 'فشل رفع الملف'];
    }
    
    private function getCarDetails($carId) {
        $stmt = $this->db->prepare("SELECT * FROM cars WHERE id = ?");
        $stmt->execute([$carId]);
        return $stmt->fetch();
    }
    
    private function getCustomerDetails($customerId) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$customerId]);
        return $stmt->fetch();
    }
    
    private function calculateDays($start, $end) {
        $startDate = new DateTime($start);
        $endDate = new DateTime($end);
        return $startDate->diff($endDate)->days + 1;
    }
    
    private function updateCarStatus($carId, $status) {
        $stmt = $this->db->prepare("UPDATE cars SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $carId]);
    }
    
    private function saveWhatsAppMessage($phone, $message, $template, $messageId) {
        $stmt = $this->db->prepare("INSERT INTO whatsapp_messages (phone_number, message_type, template_name, message_id, sent_at) 
                                   VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$phone, $template ? 'template' : 'custom', $template, $messageId]);
    }
    
    private function logActivity($action, $details) {
        if (isset($_SESSION['user_id'])) {
            $stmt = $this->db->prepare("INSERT INTO activity_log (user_id, action, details, ip_address, created_at) 
                                       VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$_SESSION['user_id'], $action, $details, $_SERVER['REMOTE_ADDR']]);
        }
    }
    
    private function sendNotification($title, $message) {
        // This can be extended to use WebSocket or database notifications
        // For now, we'll just log it
        error_log("Notification: $title - $message");
    }
    
    private function jsonResponse($success, $message = '', $data = []) {
        header('Content-Type: application/json');
        return json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
    }
}
?>