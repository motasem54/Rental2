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
    <title>إعدادات المظهر</title>
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
                <h2><i class="fas fa-palette me-2"></i>إعدادات المظهر</h2>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-right me-2"></i>العودة</a>
            </div>
            <div class="glass-card">
                <h5 class="mb-3">اختر اللون الرئيسي</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="theme-option" onclick="changeTheme('#FF5722')" style="background: #FF5722; height: 100px; border-radius: 10px; cursor: pointer;"></div>
                        <p class="text-center mt-2">برتقالي (افتراضي)</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="theme-option" onclick="changeTheme('#2196F3')" style="background: #2196F3; height: 100px; border-radius: 10px; cursor: pointer;"></div>
                        <p class="text-center mt-2">أزرق</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="theme-option" onclick="changeTheme('#4CAF50')" style="background: #4CAF50; height: 100px; border-radius: 10px; cursor: pointer;"></div>
                        <p class="text-center mt-2">أخضر</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="theme-option" onclick="changeTheme('#9C27B0')" style="background: #9C27B0; height: 100px; border-radius: 10px; cursor: pointer;"></div>
                        <p class="text-center mt-2">بنفسجي</p>
                    </div>
                </div>
                <hr>
                <h5 class="mb-3">خيارات إضافية</h5>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="darkMode" checked>
                    <label class="form-check-label" for="darkMode">الوضع المظلم</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="animations" checked>
                    <label class="form-check-label" for="animations">تفعيل الحركات</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="sounds">
                    <label class="form-check-label" for="sounds">الأصوات</label>
                </div>
                <button class="btn btn-orange mt-3" onclick="saveTheme()">
                    <i class="fas fa-save me-2"></i>حفظ الإعدادات
                </button>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/app.js"></script>
    <script>
    function changeTheme(color) {
        document.documentElement.style.setProperty('--primary', color);
        alert('تم تغيير اللون الرئيسي');
    }
    function saveTheme() {
        alert('تم حفظ إعدادات المظهر بنجاح');
    }
    </script>
</body>
</html>
