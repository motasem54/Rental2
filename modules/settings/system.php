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
    <title>إعدادات النظام</title>
    <link rel="stylesheet" href="../../assets/css/glassmorphism.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/rtl.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php $currentDir = 'settings'; include '../../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-cog me-2"></i>إعدادات النظام</h2>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-right me-2"></i>العودة</a>
            </div>
            <div class="glass-card">
                <form id="systemSettingsForm">
                    <h5 class="mb-3">الإعدادات العامة</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">اسم الشركة</label>
                            <input type="text" class="form-control glass-input" name="company_name" value="شركة تأجير السيارات">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" class="form-control glass-input" name="email" value="info@rentalsys.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="tel" class="form-control glass-input" name="phone" value="+972599000000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">العملة</label>
                            <select class="form-select glass-input" name="currency">
                                <option value="ILS" selected>شيكل (₪)</option>
                                <option value="USD">دولار ($)</option>
                                <option value="EUR">يورو (€)</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <h5 class="mb-3">إعدادات البريد الإلكتروني</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SMTP Host</label>
                            <input type="text" class="form-control glass-input" name="smtp_host">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SMTP Port</label>
                            <input type="number" class="form-control glass-input" name="smtp_port" value="587">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-orange">
                        <i class="fas fa-save me-2"></i>حفظ الإعدادات
                    </button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/app.js"></script>
    <script>
    document.getElementById('systemSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'update_settings');
        fetch('../../api/settings.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(d => {
                if (d.success) alert('تم حفظ الإعدادات بنجاح');
                else alert('خطأ: ' + d.message);
            });
    });
    </script>
</body>
</html>
