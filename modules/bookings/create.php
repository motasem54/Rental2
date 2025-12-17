<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) { header('Location: ../../login.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حجز جديد</title>
    <link rel="stylesheet" href="../../assets/css/glassmorphism.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/rtl.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
    <?php $currentDir = 'bookings'; include '../../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-calendar-plus me-2"></i>إنشاء حجز جديد</h2>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-right me-2"></i>العودة</a>
            </div>
            <div class="glass-card">
                <form id="createBookingForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">العميل *</label>
                            <select class="form-select glass-input" name="customer_id" required>
                                <option value="">اختر العميل</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">السيارة *</label>
                            <select class="form-select glass-input" name="car_id" required>
                                <option value="">اختر السيارة</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">تاريخ البدء *</label>
                            <input type="text" class="form-control glass-input datepicker" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">تاريخ الانتهاء *</label>
                            <input type="text" class="form-control glass-input datepicker" name="end_date" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea class="form-control glass-input" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-orange">
                        <i class="fas fa-save me-2"></i>إنشاء الحجز
                    </button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="../../assets/js/app.js"></script>
    <script>
    flatpickr('.datepicker', { dateFormat: 'Y-m-d', minDate: 'today' });
    document.getElementById('createBookingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'create');
        fetch('../../api/bookings.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    alert('تم إنشاء الحجز بنجاح');
                    window.location.href = 'index.php';
                } else alert('خطأ: ' + d.message);
            });
    });
    </script>
</body>
</html>
