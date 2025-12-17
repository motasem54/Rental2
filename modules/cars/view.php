<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) { header('Location: ../../login.php'); exit(); }
$car_id = $_GET['id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل السيارة</title>
    <link rel="stylesheet" href="../../assets/css/glassmorphism.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/rtl.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php $currentDir = 'cars'; include '../../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-car me-2"></i>تفاصيل السيارة</h2>
                <div>
                    <a href="edit.php?id=<?php echo $car_id; ?>" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-1"></i>تعديل
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-2"></i>العودة
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="glass-card p-4">
                        <div class="text-center mb-4">
                            <i class="fas fa-car fa-5x text-primary"></i>
                        </div>
                        <h3 id="carName" class="text-center">--</h3>
                        <p class="text-center text-muted" id="carYear">--</p>
                        <div class="text-center">
                            <span class="badge bg-success" id="carStatus">متاح</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="glass-card p-4">
                        <h5 class="mb-3">معلومات السيارة</h5>
                        <div class="mb-2"><strong>رقم اللوحة:</strong> <span id="carPlate">--</span></div>
                        <div class="mb-2"><strong>السعر اليومي:</strong> <span id="carRate">--</span> ₪</div>
                        <div class="mb-2"><strong>اللون:</strong> <span id="carColor">--</span></div>
                        <div class="mb-2"><strong>نوع الوقود:</strong> <span id="carFuel">--</span></div>
                    </div>
                    <div class="glass-card p-4 mt-4">
                        <h5 class="mb-3">إحصائيات</h5>
                        <div class="mb-2"><strong>عدد الحجوزات:</strong> <span id="carBookings">0</span></div>
                        <div class="mb-2"><strong>الإيرادات الكلية:</strong> <span id="carRevenue">0</span> ₪</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/app.js"></script>
    <script>
    const carId = <?php echo $car_id; ?>;
    fetch(\`../../api/cars.php?action=get&id=\${carId}\`)
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                const car = d.data;
                document.getElementById('carName').textContent = \`\${car.brand} \${car.model}\`;
                document.getElementById('carYear').textContent = 'موديل ' + car.year;
                document.getElementById('carPlate').textContent = car.plate_number;
                document.getElementById('carRate').textContent = car.daily_rate;
                document.getElementById('carStatus').textContent = car.status === 'available' ? 'متاح' : car.status === 'rented' ? 'مؤجر' : 'صيانة';
                document.getElementById('carColor').textContent = car.color || 'غير محدد';
                document.getElementById('carFuel').textContent = car.fuel_type || 'بنزين';
            }
        });
    </script>
</body>
</html>
