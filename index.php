<?php
// index.php
require_once 'config/database.php';
require_once 'core/Auth.php';

$auth = new Auth();

if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام تأجير السيارات المتقدم</title>
    
    <!-- Glassmorphism CSS -->
    <link rel="stylesheet" href="assets/css/glassmorphism.css">
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/main.css">
    <!-- RTL CSS -->
    <link rel="stylesheet" href="assets/css/rtl.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #FF5722;
            --primary-dark: #E64A19;
            --dark: #121212;
            --glass: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }
        
        body {
            background: linear-gradient(135deg, #121212 0%, #1a1a1a 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
            overflow-x: hidden;
        }
        
        .glass-container {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .btn-orange {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-orange:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 87, 34, 0.3);
        }
        
        .floating-bubbles {
            position: fixed;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }
        
        .bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 87, 34, 0.1);
            animation: float 15s infinite linear;
        }
        
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(-1000px) rotate(720deg); }
        }
    </style>
</head>
<body>
    <!-- Floating Bubbles Background -->
    <div class="floating-bubbles">
        <?php for($i = 0; $i < 20; $i++): ?>
            <div class="bubble" style="
                width: <?= rand(50, 200) ?>px;
                height: <?= rand(50, 200) ?>px;
                left: <?= rand(0, 100) ?>%;
                top: <?= rand(100, 120) ?>%;
                animation-delay: <?= rand(0, 15) ?>s;
                opacity: <?= rand(5, 20) / 100 ?>;
            "></div>
        <?php endfor; ?>
    </div>
    
    <!-- Main Container -->
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-lg-8">
                <div class="glass-container p-5">
                    <div class="row align-items-center">
                        <!-- Left Side - Hero Section -->
                        <div class="col-md-6 text-center mb-4 mb-md-0">
                            <div class="hero-content">
                                <div class="logo mb-4">
                                    <i class="fas fa-car fa-5x text-orange mb-3"></i>
                                    <h1 class="display-4 fw-bold">نظام تأجير السيارات</h1>
                                    <h2 class="h3 text-light">المتقدم <span class="text-orange">2024</span></h2>
                                </div>
                                
                                <div class="features">
                                    <div class="feature-item mb-3">
                                        <i class="fas fa-bolt text-success me-2"></i>
                                        <span>AJAX كامل بدون إعادة تحميل</span>
                                    </div>
                                    <div class="feature-item mb-3">
                                        <i class="fab fa-whatsapp text-success me-2"></i>
                                        <span>تكامل واتساب API</span>
                                    </div>
                                    <div class="feature-item mb-3">
                                        <i class="fas fa-chart-line text-info me-2"></i>
                                        <span>تقارير متقدمة وتحليلات</span>
                                    </div>
                                    <div class="feature-item mb-3">
                                        <i class="fas fa-shield-alt text-warning me-2"></i>
                                        <span>حماية وأمان متقدم</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-mobile-alt text-primary me-2"></i>
                                        <span>تصميم متجاوب مع جميع الأجهزة</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Side - Login Form -->
                        <div class="col-md-6">
                            <div class="auth-form">
                                <h3 class="text-center mb-4">تسجيل الدخول</h3>
                                
                                <form id="loginForm" class="ajax-form">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">اسم المستخدم أو البريد الإلكتروني</label>
                                        <input type="text" class="form-control glass-input" 
                                               id="username" name="username" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">كلمة المرور</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control glass-input" 
                                                   id="password" name="password" required>
                                            <button type="button" class="btn btn-outline-light" 
                                                    onclick="togglePassword()">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                        <label class="form-check-label" for="remember">تذكرني</label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-orange w-100 mb-3">
                                        <i class="fas fa-sign-in-alt me-2"></i> تسجيل الدخول
                                    </button>
                                    
                                    <div class="text-center">
                                        <a href="register.php" class="text-orange text-decoration-none">
                                            <i class="fas fa-user-plus me-1"></i> إنشاء حساب جديد
                                        </a>
                                    </div>
                                </form>
                                
                                <!-- Response Message -->
                                <div id="loginResponse" class="mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Footer -->
                <div class="row mt-4">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="glass-container text-center p-3">
                            <i class="fas fa-car fa-2x text-orange mb-2"></i>
                            <h4 class="mb-1" id="totalCars">0</h4>
                            <small>سيارة متاحة</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="glass-container text-center p-3">
                            <i class="fas fa-users fa-2x text-info mb-2"></i>
                            <h4 class="mb-1" id="totalCustomers">0</h4>
                            <small>عميل نشط</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="glass-container text-center p-3">
                            <i class="fas fa-calendar-check fa-2x text-success mb-2"></i>
                            <h4 class="mb-1" id="activeRentals">0</h4>
                            <small>حجز نشط</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="glass-container text-center p-3">
                            <i class="fas fa-shekel-sign fa-2x text-warning mb-2"></i>
                            <h4 class="mb-1" id="dailyIncome">0</h4>
                            <small>دخل اليوم</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript Files -->
    <script src="assets/js/app.js"></script>
    <script src="assets/js/ajax-handler.js"></script>
    
    <script>
    // Toggle Password Visibility
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = event.currentTarget.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }
    
    // Load Stats via AJAX
    async function loadStats() {
        try {
            const response = await fetch('api/stats.php');
            const stats = await response.json();
            
            if (stats.success) {
                document.getElementById('totalCars').textContent = stats.data.total_cars;
                document.getElementById('totalCustomers').textContent = stats.data.total_customers;
                document.getElementById('activeRentals').textContent = stats.data.active_rentals;
                document.getElementById('dailyIncome').textContent = stats.data.daily_income + ' ₪';
            }
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }
    
    // Load stats on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadStats();
    });
    </script>
</body>
</html>