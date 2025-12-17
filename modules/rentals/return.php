<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';
require_once '../../core/Permissions.php';
require_once '../../core/Calculations.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$permissions = new Permissions();
if (!$permissions->hasPermission($_SESSION['user_id'], 'manage_rentals')) {
    header('Location: ../../dashboard.php');
    exit();
}

$rental_id = intval($_GET['id'] ?? 0);
$db = Database::getInstance()->getConnection();

// الحصول على بيانات العملية
$stmt = $db->prepare("
    SELECT r.*, 
           c.full_name as customer_name,
           c.phone as customer_phone,
           car.brand as car_brand,
           car.model as car_model,
           car.plate_number as car_plate
    FROM rentals r
    JOIN customers c ON r.customer_id = c.id
    JOIN cars car ON r.car_id = car.id
    WHERE r.id = ? AND r.rental_status = 'active'
");

$stmt->execute([$rental_id]);
$rental = $stmt->fetch();

if (!$rental) {
    header('Location: index.php');
    exit();
}

$calculations = new RentalCalculations();
$permissions = new Permissions();

// التحقق من إمكانية تعديل العملية
if (!$permissions->canModifyRental($_SESSION['user_id'], $rental_id)) {
    echo "<script>alert('غير مصرح لك بتعديل هذه العملية'); window.location.href='view.php?id=$rental_id';</script>";
    exit();
}

// معالجة إرجاع السيارة
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual_return_datetime = $_POST['actual_return_datetime'];
    $actual_kilometers = intval($_POST['actual_kilometers']);
    $return_condition = $_POST['return_condition'];
    $notes = $_POST['notes'];
    
    // حساب الرسوم الإضافية
    $return_data = [
        'actual_return_datetime' => $actual_return_datetime,
        'actual_kilometers' => $actual_kilometers,
        'cleaning_fee' => floatval($_POST['cleaning_fee'] ?? 0),
        'fuel_fee' => floatval($_POST['fuel_fee'] ?? 0),
        'damage_fee' => floatval($_POST['damage_fee'] ?? 0)
    ];
    
    $extra_fees = $calculations->calculateAllReturnFees($rental_id, $return_data);
    $final_amount = $calculations->calculateFinalAmount($rental_id, $extra_fees);
    
    // إذا كانت العملية تتطلب تحقق
    $requires_verification = $final_amount['final_remaining'] > 5000; // مثال: أكثر من 5000 ريال
    
    if ($requires_verification && !isset($_POST['verification_code'])) {
        // توليد رمز تحقق وإرساله للمدير
        $verification_code = $permissions->generateVerificationCode($rental_id, 'return_car');
        
        // هنا يمكن إرسال الرمز عبر واتساب أو بريد للمدير
        $_SESSION['verification_required'] = true;
        $_SESSION['return_data'] = $_POST;
        $_SESSION['extra_fees'] = $extra_fees;
        $_SESSION['final_amount'] = $final_amount;
        
        header('Location: verify_return.php?id=' . $rental_id);
        exit();
    }
    
    // التحقق من الرمز إذا مطلوب
    if ($requires_verification && isset($_POST['verification_code'])) {
        if (!$permissions->verifyCode($rental_id, 'return_car', $_POST['verification_code'])) {
            echo "<script>alert('رمز التحقق غير صحيح أو منتهي الصلاحية'); window.location.reload();</script>";
            exit();
        }
    }
    
    // تحديث حالة العملية
    $update_stmt = $db->prepare("
        UPDATE rentals SET
            rental_status = 'completed',
            actual_return_datetime = ?,
            actual_return_km = ?,
            return_condition = ?,
            late_return_fee = ?,
            extra_km_fee = ?,
            cleaning_fee = ?,
            fuel_fee = ?,
            damage_fee = ?,
            total_extra_fees = ?,
            final_amount = ?,
            calculation_notes = ?,
            verified_by = ?,
            verified_at = NOW(),
            updated_at = NOW()
        WHERE id = ?
    ");
    
    // إيجاد رسوم التأخير
    $late_fee = 0;
    foreach ($extra_fees['fees'] as $fee) {
        if ($fee['type'] === 'late_return') {
            $late_fee = $fee['amount'];
            break;
        }
    }
    
    // إيجاد رسوم الكيلومترات
    $extra_km_fee = 0;
    foreach ($extra_fees['fees'] as $fee) {
        if ($fee['type'] === 'extra_kilometers') {
            $extra_km_fee = $fee['amount'];
            break;
        }
    }
    
    $update_stmt->execute([
        $actual_return_datetime,
        $actual_kilometers,
        $return_condition,
        $late_fee,
        $extra_km_fee,
        $return_data['cleaning_fee'],
        $return_data['fuel_fee'],
        $return_data['damage_fee'],
        $extra_fees['total_extra_fees'],
        $final_amount['final_total'],
        $notes,
        $requires_verification ? $_SESSION['user_id'] : null,
        $rental_id
    ]);
    
    // إضافة الرسوم الإضافية إلى جدول الرسوم
    foreach ($extra_fees['fees'] as $fee) {
        $charge_stmt = $db->prepare("
            INSERT INTO extra_charges (rental_id, charge_type, description, amount, status, created_by, created_at)
            VALUES (?, ?, ?, ?, 'approved', ?, NOW())
        ");
        
        $charge_stmt->execute([
            $rental_id,
            $fee['type'],
            $fee['description'],
            $fee['amount'],
            $_SESSION['user_id']
        ]);
    }
    
    // تحديث حالة السيارة
    $car_stmt = $db->prepare("UPDATE cars SET status = 'available' WHERE id = ?");
    $car_stmt->execute([$rental['car_id']]);
    
    // تسجيل النشاط
    $activity_stmt = $db->prepare("
        INSERT INTO activity_log (user_id, action, module, record_id, details, created_at)
        VALUES (?, ?, 'rentals', ?, ?, NOW())
    ");
    
    $activity_stmt->execute([
        $_SESSION['user_id'],
        'إرجاع سيارة',
        $rental_id,
        "تم إرجاع السيارة {$rental['car_brand']} للعملية #{$rental['rental_number']}"
    ]);
    
    echo "<script>
        alert('تم إرجاع السيارة بنجاح وتسجيل جميع الرسوم');
        window.location.href='view.php?id=$rental_id';
    </script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إرجاع سيارة - عملية #<?php echo $rental['rental_number']; ?></title>
    
    <link rel="stylesheet" href="../../assets/css/glassmorphism.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .return-card {
            border: 2px solid var(--primary);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .fee-item {
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        
        .fee-item:hover {
            background: rgba(255,87,34,0.1);
            transform: translateX(-5px);
        }
        
        .condition-radio {
            display: none;
        }
        
        .condition-label {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            border: 2px solid var(--glass-border);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .condition-radio:checked + .condition-label {
            border-color: var(--primary);
            background: rgba(255,87,34,0.1);
            color: var(--primary);
        }
        
        .calculation-result {
            background: linear-gradient(135deg, rgba(255,87,34,0.1) 0%, rgba(18,18,18,0.1) 100%);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .time-input-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .time-display {
            padding: 8px 15px;
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-undo me-2"></i> إرجاع سيارة</h2>
                    <small class="text-muted">عملية تأجير #<?php echo $rental['rental_number']; ?></small>
                </div>
                
                <div>
                    <button class="btn btn-outline-light me-2" onclick="window.history.back()">
                        <i class="fas fa-arrow-right me-2"></i> رجوع
                    </button>
                </div>
            </div>
            
            <!-- Rental Summary -->
            <div class="glass-card mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <h6>العميل</h6>
                        <p class="mb-1"><strong><?php echo htmlspecialchars($rental['customer_name']); ?></strong></p>
                        <p class="text-muted mb-0"><?php echo htmlspecialchars($rental['customer_phone']); ?></p>
                    </div>
                    
                    <div class="col-md-4">
                        <h6>السيارة</h6>
                        <p class="mb-1"><strong><?php echo htmlspecialchars($rental['car_brand'] . ' ' . $rental['car_model']); ?></strong></p>
                        <p class="text-muted mb-0"><?php echo htmlspecialchars($rental['car_plate']); ?></p>
                    </div>
                    
                    <div class="col-md-4">
                        <h6>فترة التأجير</h6>
                        <p class="mb-1">من: <?php echo date('Y/m/d', strtotime($rental['start_date'])); ?> الساعة <?php echo $rental['pickup_time']; ?></p>
                        <p class="mb-0">إلى: <?php echo date('Y/m/d', strtotime($rental['end_date'])); ?> الساعة <?php echo $rental['return_time']; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Return Form -->
            <form id="returnForm" method="POST">
                <div class="row">
                    <!-- Left Column: Return Details -->
                    <div class="col-lg-6">
                        <div class="return-card">
                            <h4 class="mb-3"><i class="fas fa-car me-2"></i> تفاصيل الإرجاع</h4>
                            
                            <!-- Actual Return Time -->
                            <div class="mb-3">
                                <label class="form-label">تاريخ ووقت الإرجاع الفعلي</label>
                                <div class="time-input-group">
                                    <input type="datetime-local" class="form-control glass-input" 
                                           id="actualReturnDatetime" name="actual_return_datetime"
                                           value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                                    <div class="time-display" id="currentTimeDisplay">
                                        <?php echo date('H:i'); ?>
                                    </div>
                                </div>
                                <small class="text-muted">يتم احتساب رسوم التأخير من الساعة <?php echo $rental['return_time']; ?></small>
                            </div>
                            
                            <!-- Actual Kilometers -->
                            <div class="mb-3">
                                <label class="form-label">عداد المسافة الفعلي (كم)</label>
                                <input type="number" class="form-control glass-input" 
                                       id="actualKilometers" name="actual_kilometers"
                                       min="<?php echo $rental['pickup_km']; ?>" 
                                       value="<?php echo $rental['pickup_km'] + 300; ?>" required>
                                <small class="text-muted">عداد الاستلام: <?php echo $rental['pickup_km']; ?> كم | المسموح: <?php echo $rental['km_allowed'] ?? 300; ?> كم</small>
                            </div>
                            
                            <!-- Return Condition -->
                            <div class="mb-3">
                                <label class="form-label">حالة السيارة عند الإرجاع</label>
                                <div class="d-flex flex-wrap">
                                    <input type="radio" name="return_condition" value="excellent" 
                                           id="condition_excellent" class="condition-radio" checked>
                                    <label for="condition_excellent" class="condition-label">
                                        <i class="fas fa-star me-2 text-success"></i> ممتازة
                                    </label>
                                    
                                    <input type="radio" name="return_condition" value="good" 
                                           id="condition_good" class="condition-radio">
                                    <label for="condition_good" class="condition-label">
                                        <i class="fas fa-check me-2 text-info"></i> جيدة
                                    </label>
                                    
                                    <input type="radio" name="return_condition" value="fair" 
                                           id="condition_fair" class="condition-radio">
                                    <label for="condition_fair" class="condition-label">
                                        <i class="fas fa-exclamation me-2 text-warning"></i> مقبولة
                                    </label>
                                    
                                    <input type="radio" name="return_condition" value="poor" 
                                           id="condition_poor" class="condition-radio">
                                    <label for="condition_poor" class="condition-label">
                                        <i class="fas fa-times me-2 text-danger"></i> سيئة
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Notes -->
                            <div class="mb-3">
                                <label class="form-label">ملاحظات الإرجاع</label>
                                <textarea class="form-control glass-input" name="notes" 
                                          rows="3" placeholder="أي ملاحظات إضافية عن حالة السيارة..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column: Extra Fees -->
                    <div class="col-lg-6">
                        <div class="return-card">
                            <h4 class="mb-3"><i class="fas fa-money-bill-wave me-2"></i> الرسوم الإضافية</h4>
                            
                            <!-- Cleaning Fee -->
                            <div class="fee-item">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <i class="fas fa-broom me-2 text-info"></i>
                                        <strong>رسوم تنظيف</strong>
                                    </div>
                                    <div>
                                        <input type="number" class="form-control form-control-sm glass-input" 
                                               style="width: 120px;" name="cleaning_fee" value="0" min="0" step="50">
                                        <small class="text-muted">ريال</small>
                                    </div>
                                </div>
                                <small class="text-muted">في حال إرجاع السيارة غير نظيفة</small>
                            </div>
                            
                            <!-- Fuel Fee -->
                            <div class="fee-item">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <i class="fas fa-gas-pump me-2 text-warning"></i>
                                        <strong>رسوم وقود</strong>
                                    </div>
                                    <div>
                                        <input type="number" class="form-control form-control-sm glass-input" 
                                               style="width: 120px;" name="fuel_fee" value="0" min="0" step="10">
                                        <small class="text-muted">ريال</small>
                                    </div>
                                </div>
                                <small class="text-muted">في حال إرجاع السيارة بدون صندوق وقود كامل</small>
                            </div>
                            
                            <!-- Damage Fee -->
                            <div class="fee-item">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <i class="fas fa-car-crash me-2 text-danger"></i>
                                        <strong>رسوم أضرار</strong>
                                    </div>
                                    <div>
                                        <input type="number" class="form-control form-control-sm glass-input" 
                                               style="width: 120px;" name="damage_fee" value="0" min="0" step="100">
                                        <small class="text-muted">ريال</small>
                                    </div>
                                </div>
                                <small class="text-muted">في حال وجود خدوش أو أضرار جديدة</small>
                            </div>
                            
                            <!-- Auto Calculations -->
                            <div class="calculation-result">
                                <h5 class="mb-3">الحسابات التلقائية</h5>
                                
                                <div class="mb-2 d-flex justify-content-between">
                                    <span>رسوم التأخير:</span>
                                    <span id="lateFeeResult">0 ريال</span>
                                </div>
                                
                                <div class="mb-2 d-flex justify-content-between">
                                    <span>كيلومترات إضافية:</span>
                                    <span id="extraKmResult">0 كم (0 ريال)</span>
                                </div>
                                
                                <div class="mb-3 d-flex justify-content-between">
                                    <span>إجمالي الرسوم الإضافية:</span>
                                    <strong id="totalExtraFees">0 ريال</strong>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="button" class="btn btn-orange" onclick="calculateFees()">
                                        <i class="fas fa-calculator me-2"></i> حساب الرسوم
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Final Calculation Preview -->
                            <div class="calculation-result mt-3" id="finalCalculation" style="display: none;">
                                <h5 class="mb-3">الملخص النهائي</h5>
                                
                                <div class="mb-2 d-flex justify-content-between">
                                    <span>قيمة التأجير الأصلية:</span>
                                    <span id="originalAmount"><?php echo number_format($rental['total_rental_amount'], 2); ?> ريال</span>
                                </div>
                                
                                <div class="mb-2 d-flex justify-content-between">
                                    <span>الدفعة المقدمة:</span>
                                    <span id="depositPaid"><?php echo number_format($rental['deposit_amount'], 2); ?> ريال</span>
                                </div>
                                
                                <div class="mb-2 d-flex justify-content-between">
                                    <span>المتبقي أصلاً:</span>
                                    <span id="remainingOriginal">0 ريال</span>
                                </div>
                                
                                <div class="mb-2 d-flex justify-content-between">
                                    <span>الرسوم الإضافية:</span>
                                    <span id="extraFeesTotal">0 ريال</span>
                                </div>
                                
                                <div class="mb-3 pt-2 border-top d-flex justify-content-between">
                                    <span><strong>المبلغ النهائي المستحق:</strong></span>
                                    <strong class="text-orange" id="finalAmountDue">0 ريال</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-check-circle me-2"></i> تأكيد إرجاع السيارة
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- JavaScript Files -->
    <script src="../../assets/js/app.js"></script>
    <script src="../../assets/js/ajax-handler.js"></script>
    
    <script>
    // تحديث الوقت الحالي
    function updateCurrentTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('ar-EG', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
        document.getElementById('currentTimeDisplay').textContent = timeString;
    }
    
    // تحديث الوقت كل دقيقة
    setInterval(updateCurrentTime, 60000);
    updateCurrentTime();
    
    // حساب الرسوم
    async function calculateFees() {
        const rentalId = <?php echo $rental_id; ?>;
        const actualReturnDatetime = document.getElementById('actualReturnDatetime').value;
        const actualKilometers = document.getElementById('actualKilometers').value;
        
        if (!actualReturnDatetime || !actualKilometers) {
            alert('يرجى تعبئة جميع الحقول المطلوبة');
            return;
        }
        
        try {
            // حساب رسوم التأخير
            const lateResponse = await fetch(`api/calculations.php?action=calculate_late_fees&rental_id=${rentalId}&return_datetime=${actualReturnDatetime}`);
            const lateData = await lateResponse.json();
            
            // حساب رسوم الكيلومترات
            const kmResponse = await fetch(`api/calculations.php?action=calculate_km_fees&rental_id=${rentalId}&actual_km=${actualKilometers}`);
            const kmData = await kmResponse.json();
            
            // تحديث النتائج
            document.getElementById('lateFeeResult').textContent = 
                lateData.is_late ? `${lateData.total_late_fee} ريال (${lateData.late_hours} ساعة)` : '0 ريال';
            
            document.getElementById('extraKmResult').textContent = 
                kmData.has_extra_kilometers ? 
                `${kmData.extra_kilometers} كم (${kmData.total_extra_km_fee} ريال)` : 
                '0 كم (0 ريال)';
            
            // حساب الرسوم اليدوية
            const cleaningFee = parseFloat(document.querySelector('[name="cleaning_fee"]').value) || 0;
            const fuelFee = parseFloat(document.querySelector('[name="fuel_fee"]').value) || 