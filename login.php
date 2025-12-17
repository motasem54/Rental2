<?php
// login.php
require_once 'config/database.php';
require_once 'core/Auth.php';

$auth = new Auth();

// If already logged in, redirect to dashboard
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

// Handle login request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'الرجاء إدخال اسم المستخدم وكلمة المرور';
    } else {
        $result = $auth->login($username, $password);

        if ($result['success']) {
            header('Location: dashboard.php');
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>*3,JD 'D/.HD - F8'E *#,J1 'D3J'1'*</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/glassmorphism.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/rtl.css">

    <style>
        :root {
            --primary: #FF5722;
            --primary-dark: #E64A19;
            --dark: #121212;
        }

        body {
            background: linear-gradient(135deg, #121212 0%, #1a1a1a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
            position: relative;
            overflow: hidden;
        }

        /* Animated Background Bubbles */
        .glass-bubbles {
            position: fixed;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }

        .glass-bubble {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 87, 34, 0.15) 0%, rgba(255, 87, 34, 0) 70%);
            animation: floatBubble 20s infinite linear;
        }

        .glass-bubble:nth-child(1) {
            width: 80px;
            height: 80px;
            left: 10%;
            animation-duration: 15s;
        }

        .glass-bubble:nth-child(2) {
            width: 120px;
            height: 120px;
            left: 70%;
            animation-duration: 20s;
            animation-delay: 2s;
        }

        .glass-bubble:nth-child(3) {
            width: 100px;
            height: 100px;
            left: 40%;
            animation-duration: 18s;
            animation-delay: 4s;
        }

        .glass-bubble:nth-child(4) {
            width: 150px;
            height: 150px;
            left: 80%;
            animation-duration: 22s;
            animation-delay: 1s;
        }

        .glass-bubble:nth-child(5) {
            width: 90px;
            height: 90px;
            left: 25%;
            animation-duration: 17s;
            animation-delay: 3s;
        }

        @keyframes floatBubble {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }

            10% {
                opacity: 0.5;
            }

            90% {
                opacity: 0.5;
            }

            100% {
                transform: translateY(-100vh) rotate(720deg);
                opacity: 0;
            }
        }

        .login-container {
            position: relative;
            z-index: 1;
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(255, 87, 34, 0.7);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 20px rgba(255, 87, 34, 0);
            }
        }

        .logo-container h2 {
            font-weight: bold;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 5px;
        }

        .logo-container p {
            color: #ccc;
            font-size: 0.9rem;
        }

        .form-control,
        .form-select {
            background: rgba(255, 255, 255, 0.05) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: white !important;
            border-radius: 10px !important;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            background: rgba(255, 255, 255, 0.1) !important;
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 0.25rem rgba(255, 87, 34, 0.25) !important;
            color: white !important;
        }

        .form-control::placeholder {
            color: #999;
        }

        .input-group-text {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--primary);
            border-radius: 10px 0 0 10px !important;
        }

        .input-group .form-control {
            border-right: none !important;
            border-radius: 0 10px 10px 0 !important;
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: 0.5s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 87, 34, 0.4);
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 25px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .divider span {
            padding: 0 10px;
            color: #999;
            font-size: 0.9rem;
        }

        .alert {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: white;
            padding: 12px 15px;
            margin-bottom: 20px;
        }

        .alert-danger {
            border-right: 4px solid #dc3545;
            background: rgba(220, 53, 69, 0.1);
        }

        .alert-success {
            border-right: 4px solid #28a745;
            background: rgba(40, 167, 69, 0.1);
        }

        .form-check-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .form-check-label {
            color: #ccc;
        }

        .text-link {
            color: var(--primary);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .text-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .social-login {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-social {
            flex: 1;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px;
            border-radius: 10px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-social:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            color: #999;
            font-size: 0.85rem;
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 30px 20px;
            }

            .logo-icon {
                width: 60px;
                height: 60px;
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <!-- Animated Background Bubbles -->
    <div class="glass-bubbles">
        <div class="glass-bubble"></div>
        <div class="glass-bubble"></div>
        <div class="glass-bubble"></div>
        <div class="glass-bubble"></div>
        <div class="glass-bubble"></div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <!-- Logo -->
            <div class="logo-container">
                <div class="logo-icon">
                    <i class="fas fa-car"></i>
                </div>
                <h2>F8'E *#,J1 'D3J'1'*</h2>
                <p>E1-('K (C! 3,QD /.HDC DDE*'(9)</p>
            </div>

            <!-- Error Message -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label class="form-label">'3E 'DE3*./E #H 'D(1J/ 'D%DC*1HFJ</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" name="username" class="form-control" placeholder="#/.D '3E 'DE3*./E" required
                            autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">CDE) 'DE1H1</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="password" id="password" class="form-control"
                            placeholder="#/.D CDE) 'DE1H1" required>
                        <span class="input-group-text" onclick="togglePassword()"
                            style="cursor: pointer; border-radius: 0 10px 10px 0 !important;">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">
                            *0C1FJ
                        </label>
                    </div>
                    <a href="#" class="text-link">F3J* CDE) 'DE1H1</a>
                </div>

                <button type="submit" name="login" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    *3,JD 'D/.HD
                </button>
            </form>

            <!-- Divider -->
            <div class="divider">
                <span>#H 3,D /.HDC 9(1</span>
            </div>

            <!-- Social Login -->
            <div class="social-login">
                <a href="#" class="btn-social">
                    <i class="fab fa-google"></i>
                    Google
                </a>
                <a href="#" class="btn-social">
                    <i class="fab fa-facebook-f"></i>
                    Facebook
                </a>
            </div>

            <!-- Register Link -->
            <div class="text-center mt-4">
                <p class="mb-0">
                    DJ3 D/JC -3'(
                    <a href="register.php" class="text-link">
                        <strong>3,QD 'D"F</strong>
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-text">
            <p class="mb-1">� 2024 F8'E *#,J1 'D3J'1'* 'DE*B/E - ,EJ9 'D-BHB E-AH8)</p>
            <p class="mb-0">
                <small>
                    <a href="#" class="text-link">'D41H7 H'D#-C'E</a> |
                    <a href="#" class="text-link">3J'3) 'D.5H5J)</a>
                </small>
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle Password Visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>

</html>