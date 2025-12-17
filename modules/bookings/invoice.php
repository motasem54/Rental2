<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) { header('Location: ../../login.php'); exit(); }
$booking_id = $_GET['id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة الحجز</title>
    <link rel="stylesheet" href="../../assets/css/glassmorphism.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/rtl.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php $currentDir = 'bookings'; include '../../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-file-invoice me-2"></i>فاتورة الحجز #<?php echo $booking_id; ?></h2>
                <div>
                    <button class="btn btn-primary me-2" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>طباعة
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-2"></i>العودة
                    </a>
                </div>
            </div>
            <div class="glass-card p-4">
                <div class="text-center mb-4">
                    <h3>نظام تأجير السيارات المتقدم</h3>
                    <p class="text-muted">فاتورة رقم: <?php echo $booking_id; ?></p>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h5>معلومات العميل</h5>
                        <p id="customerInfo">جاري التحميل...</p>
                    </div>
                    <div class="col-md-6">
                        <h5>معلومات السيارة</h5>
                        <p id="carInfo">جاري التحميل...</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h5>تفاصيل الحجز</h5>
                        <table class="table table-dark">
                            <tr>
                                <td>تاريخ البدء</td>
                                <td id="startDate">--</td>
                            </tr>
                            <tr>
                                <td>تاريخ الانتهاء</td>
                                <td id="endDate">--</td>
                            </tr>
                            <tr>
                                <td>عدد الأيام</td>
                                <td id="days">0</td>
                            </tr>
                            <tr>
                                <td>السعر اليومي</td>
                                <td id="dailyRate">0 ₪</td>
                            </tr>
                            <tr class="table-active">
                                <th>المجموع الكلي</th>
                                <th id="totalAmount">0 ₪</th>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/app.js"></script>
    <script>
    const bookingId = <?php echo $booking_id; ?>;
    fetch(\`../../api/bookings.php?action=get&id=\${bookingId}\`)
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                const booking = d.data;
                document.getElementById('customerInfo').textContent = booking.customer_name;
                document.getElementById('carInfo').textContent = booking.car_name;
                document.getElementById('startDate').textContent = booking.start_date;
                document.getElementById('endDate').textContent = booking.end_date;
                document.getElementById('days').textContent = booking.days;
                document.getElementById('dailyRate').textContent = booking.daily_rate + ' ₪';
                document.getElementById('totalAmount').textContent = booking.total + ' ₪';
            }
        });
    </script>
</body>
</html>
