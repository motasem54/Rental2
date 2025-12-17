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
    <title>إضافة سيارة جديدة</title>
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
                <h2><i class="fas fa-car-plus me-2"></i>إضافة سيارة جديدة</h2>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-right me-2"></i>العودة</a>
            </div>
            <div class="glass-card">
                <form id="addCarForm">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">الشركة المصنعة *</label>
                            <input type="text" class="form-control glass-input" name="brand" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">الموديل *</label>
                            <input type="text" class="form-control glass-input" name="model" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">السنة *</label>
                            <input type="number" class="form-control glass-input" name="year" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">رقم اللوحة *</label>
                            <input type="text" class="form-control glass-input" name="plate_number" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">السعر اليومي (₪)</label>
                            <input type="number" class="form-control glass-input" name="daily_rate" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">الحالة</label>
                            <select class="form-select glass-input" name="status">
                                <option value="available">متاح</option>
                                <option value="rented">مؤجر</option>
                                <option value="maintenance">صيانة</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-orange">
                        <i class="fas fa-save me-2"></i>حفظ السيارة
                    </button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/app.js"></script>
    <script>
    document.getElementById('addCarForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'create');
        fetch('../../api/cars.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    alert('تم إضافة السيارة بنجاح');
                    window.location.href = 'index.php';
                } else alert('خطأ: ' + d.message);
            });
    });
    </script>
</body>
</html>
