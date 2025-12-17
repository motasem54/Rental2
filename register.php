<?php
// register.php
require_once 'config/database.php';
require_once 'core/Auth.php';
require_once 'core/Validator.php';

$auth = new Auth();

// If already logged in, redirect to dashboard
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';
$errors = [];

// Handle registration request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $validator = new Validator();

    $data = [
        'username' => trim($_POST['username']),
        'email' => trim($_POST['email']),
        'full_name' => trim($_POST['full_name']),
        'phone' => trim($_POST['phone']),
        'password' => $_POST['password'],
        'confirm_password' => $_POST['confirm_password']
    ];

    // Validate inputs
    if (empty($data['username'])) {
        $errors['username'] = 'اسم المستخدم مطلوب';
    }
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'البريد الالكتروني مطلوب';
    }

    if (empty($data['full_name'])) {
        $errors['full_name'] = 'الاسم الكامل مطلوب';
    }

    if (empty($data['phone']) || !preg_match('/^[0-9+\-\s()]+$/', $data['phone'])) {
        $errors['phone'] = 'رقم الهاتف مطلوب    ';
    }

    if (empty($data['password']) || strlen($data['password']) < 6) {
        $errors['password'] = 'كلمة المرور يجب ان تكون اكثر من 6 احرف';
    }

    if ($data['password'] !== $data['confirm_password']) {
        $errors['confirm_password'] = 'كلمة المرور غير متطابقة';
    }

    if (empty($errors)) {
        $result = $auth->register($data);

        if ($result['success']) {
            $success = 'تم انشاء الحساب بنجاح';
            // Clear form data
            $data = [];
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'حدث خطأ اثناء التسجيل';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>%F4'! -3'( ,/J/ - F8'E *#,J1 'D3J'1'*</title>

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
            overflow-x: hidden;
            padding: 40px 0;
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

        .register-container {
            position: relative;
            z-index: 1;
            max-width: 550px;
            width: 100%;
            padding: 20px;
        }

        .register-card {
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
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 2rem;
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
            font-size: 1.5rem;
        }

        .logo-container p {
            color: #ccc;
            font-size: 0.85rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: white !important;
            border-radius: 10px !important;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1) !important;
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 0.25rem rgba(255, 87, 34, 0.25) !important;
            color: white !important;
        }

        .form-control::placeholder {
            color: #999;
        }

        .form-control.is-invalid {
            border-color: #dc3545 !important;
        }

        .invalid-feedback {
            display: block;
            color: #ff6b6b;
            font-size: 0.85rem;
            margin-top: 5px;
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

        .form-label {
            color: #ddd;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .btn-register {
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

        .btn-register::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: 0.5s;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 87, 34, 0.4);
        }

        .btn-register:hover::before {
            left: 100%;
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

        .text-link {
            color: var(--primary);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .text-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .password-strength {
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .footer-text {
            text-align: center;
            margin-top: 15px;
            color: #999;
            font-size: 0.85rem;
        }

        @media (max-width: 576px) {
            .register-card {
                padding: 30px 20px;
            }

            .logo-icon {
                width: 60px;
                height: 60px;
                font-size: 1.8rem;
            }

            .logo-container h2 {
                font-size: 1.3rem;
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
    </div>

    <div class="register-container">
        <div class="register-card">
            <!-- Logo -->
            <div class="logo-container">
                <div class="logo-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h2>%F4'! -3'( ,/J/</h2>
                <p>'F6E %DI F8'E *#,J1 'D3J'1'* 'DE*B/E</p>
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
                    <br>
                    <a href="login.php" class="text-link mt-2 d-inline-block">
                        <i class="fas fa-sign-in-alt me-1"></i> *3,JD 'D/.HD 'D"F
                    </a>
                </div>
            <?php endif; ?>

            <!-- Register Form -->
            <form method="POST" action="register.php" id="registerForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">'D'3E 'DC'ED</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-id-card"></i>
                            </span>
                            <input type="text" name="full_name"
                                class="form-control <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>"
                                placeholder="#/.D '3EC 'DC'ED"
                                value="<?php echo isset($data['full_name']) ? htmlspecialchars($data['full_name']) : ''; ?>"
                                required>
                        </div>
                        <?php if (isset($errors['full_name'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['full_name']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">'3E 'DE3*./E</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" name="username"
                                class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>"
                                placeholder="'.*1 '3E E3*./E"
                                value="<?php echo isset($data['username']) ? htmlspecialchars($data['username']) : ''; ?>"
                                required>
                        </div>
                        <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['username']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">'D(1J/ 'D%DC*1HFJ</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" name="email"
                                class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                                placeholder="example@domain.com"
                                value="<?php echo isset($data['email']) ? htmlspecialchars($data['email']) : ''; ?>"
                                required>
                        </div>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">1BE 'DG'*A</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-phone"></i>
                            </span>
                            <input type="tel" name="phone"
                                class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>"
                                placeholder="+972599999999"
                                value="<?php echo isset($data['phone']) ? htmlspecialchars($data['phone']) : ''; ?>"
                                required>
                        </div>
                        <?php if (isset($errors['phone'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['phone']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">CDE) 'DE1H1</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="password" id="password"
                            class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                            placeholder="#/.D CDE) E1H1 BHJ)" required>
                        <span class="input-group-text" onclick="togglePassword('password')"
                            style="cursor: pointer; border-radius: 0 10px 10px 0 !important;">
                            <i class="fas fa-eye" id="toggleIcon1"></i>
                        </span>
                    </div>
                    <div class="password-strength" id="passwordStrength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    <small class="text-muted" id="strengthText">BH) CDE) 'DE1H1</small>
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">*#CJ/ CDE) 'DE1H1</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock-open"></i>
                        </span>
                        <input type="password" name="confirm_password" id="confirm_password"
                            class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>"
                            placeholder="#9/ %/.'D CDE) 'DE1H1" required>
                        <span class="input-group-text" onclick="togglePassword('confirm_password')"
                            style="cursor: pointer; border-radius: 0 10px 10px 0 !important;">
                            <i class="fas fa-eye" id="toggleIcon2"></i>
                        </span>
                    </div>
                    <?php if (isset($errors['confirm_password'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label" for="terms" style="color: #ccc;">
                            #H'AB 9DI <a href="#" class="text-link">'D41H7 H'D#-C'E</a> H<a href="#"
                                class="text-link">3J'3) 'D.5H5J)</a>
                        </label>
                    </div>
                </div>

                <button type="submit" name="register" class="btn btn-register">
                    <i class="fas fa-user-plus me-2"></i>
                    %F4'! 'D-3'(
                </button>
            </form>

            <!-- Login Link -->
            <div class="text-center mt-4">
                <p class="mb-0">
                    D/JC -3'( ('DA9D
                    <a href="login.php" class="text-link">
                        <strong>3,QD /.HDC</strong>
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-text">
            <p class="mb-0">� 2024 F8'E *#,J1 'D3J'1'* 'DE*B/E - ,EJ9 'D-BHB E-AH8)</p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle Password Visibility
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const iconNumber = inputId === 'password' ? '1' : '2';
            const toggleIcon = document.getElementById('toggleIcon' + iconNumber);

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

        // Password Strength Checker
        document.getElementById('password').addEventListener('input', function (e) {
            const password = e.target.value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');

            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            const colors = ['#dc3545', '#ffc107', '#17a2b8', '#28a745', '#20c997'];
            const texts = ['69JA) ,/'K', '69JA)', 'E * H37) ', 'BHJ) ', 'BHJ) ,/'K'];
        const widths = ['20%', '40%', '60%', '80%', '100%'];

        strengthBar.style.width = widths[strength];
        strengthBar.style.background = colors[strength];
        strengthText.textContent = 'BH) CDE) 'DE1H1: ' + texts[strength];
        strengthText.style.color = colors[strength];
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (!alert.classList.contains('alert-success')) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            });
        }, 5000);
    </script>
</body>

</html>