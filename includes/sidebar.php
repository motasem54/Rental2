<?php
// includes/sidebar.php
// Get current page to highlight active menu
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// Get user info safely
$userName = isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'مستخدم';
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : 'customer';
$roleText = $userRole == 'admin' ? 'مدير النظام' : ($userRole == 'employee' ? 'موظف' : 'عميل');
?>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="logo">
        <i class="fas fa-car fa-2x text-orange"></i>
        <h4 class="mt-2 sidebar-text">تأجير السيارات</h4>
    </div>

    <!-- User Info -->
    <div class="user-info">
        <div class="user-avatar">
            <i class="fas fa-user"></i>
        </div>
        <h6 class="mb-1 sidebar-text"><?php echo $userName; ?></h6>
        <small class="text-muted sidebar-text"><?php echo $roleText; ?></small>
    </div>

    <!-- Navigation Menu -->
    <nav class="nav flex-column">
        <!-- Dashboard -->
        <a href="../../dashboard.php" class="nav-link <?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span class="sidebar-text">لوحة التحكم</span>
        </a>

        <!-- Cars Management -->
        <a href="../cars/index.php" class="nav-link <?php echo ($currentDir == 'cars') ? 'active' : ''; ?>">
            <i class="fas fa-car"></i>
            <span class="sidebar-text">إدارة السيارات</span>
        </a>

        <!-- Customers -->
        <a href="../customers/index.php" class="nav-link <?php echo ($currentDir == 'customers') ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span class="sidebar-text">العملاء</span>
        </a>

        <!-- Bookings -->
        <a href="../bookings/index.php" class="nav-link <?php echo ($currentDir == 'bookings') ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i>
            <span class="sidebar-text">الحجوزات</span>
        </a>

        <!-- Rentals -->
        <a href="../rentals/index.php" class="nav-link <?php echo ($currentDir == 'rentals') ? 'active' : ''; ?>">
            <i class="fas fa-handshake"></i>
            <span class="sidebar-text">عقود الإيجار</span>
        </a>

        <!-- Maintenance -->
        <a href="../maintenance/index.php"
            class="nav-link <?php echo ($currentDir == 'maintenance') ? 'active' : ''; ?>">
            <i class="fas fa-tools"></i>
            <span class="sidebar-text">الصيانة</span>
        </a>

        <!-- Insurance -->
        <a href="../insurance/index.php" class="nav-link <?php echo ($currentDir == 'insurance') ? 'active' : ''; ?>">
            <i class="fas fa-shield-alt"></i>
            <span class="sidebar-text">التأمين</span>
        </a>

        <!-- Reports -->
        <a href="../reports/financial.php" class="nav-link <?php echo ($currentDir == 'reports') ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i>
            <span class="sidebar-text">التقارير</span>
        </a>

        <!-- Settings (Admin Only) -->
        <?php if ($userRole == 'admin'): ?>
            <a href="../settings/index.php" class="nav-link <?php echo ($currentDir == 'settings') ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span class="sidebar-text">الإعدادات</span>
            </a>
        <?php endif; ?>

        <!-- Divider -->
        <hr class="my-3" style="border-color: var(--glass-border);">

        <!-- Logout -->
        <a href="../../logout.php" class="nav-link text-danger">
            <i class="fas fa-sign-out-alt"></i>
            <span class="sidebar-text">تسجيل الخروج</span>
        </a>
    </nav>

    <!-- System Status -->
    <div class="glass-card m-3 mt-4" style="padding: 15px;">
        <h6 class="mb-2">
            <i class="fas fa-server me-2"></i>
            <span class="sidebar-text">حالة النظام</span>
        </h6>
        <div class="system-status">
            <div class="d-flex justify-content-between mb-2">
                <small class="sidebar-text">الأداء</small>
                <small id="systemPerformance">85%</small>
            </div>
            <div class="progress" style="height: 5px; background: rgba(255, 140, 66, 0.1);">
                <div class="progress-bar" id="performanceBar" style="width: 85%; background: var(--primary);"></div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Menu Toggle Button -->
<button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<style>
    .sidebar {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-left: 1px solid var(--glass-border);
        box-shadow: -2px 0 20px rgba(255, 140, 66, 0.08);
        height: 100vh;
        position: fixed;
        right: 0;
        top: 0;
        width: 260px;
        padding-top: 20px;
        overflow-y: auto;
        z-index: 1000;
        transition: all 0.3s ease;
    }

    .logo {
        padding: 20px;
        text-align: center;
        border-bottom: 1px solid var(--glass-border);
        margin-bottom: 20px;
    }

    .logo .text-orange {
        color: var(--primary);
    }

    .logo h4 {
        color: var(--text-primary);
        font-weight: 700;
    }

    .user-info {
        padding: 15px;
        text-align: center;
        border-bottom: 1px solid var(--glass-border);
        margin-bottom: 20px;
    }

    .user-avatar {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 1.8rem;
        color: white;
        box-shadow: 0 4px 15px rgba(255, 140, 66, 0.3);
    }

    .user-info h6 {
        color: var(--text-primary);
        font-weight: 600;
    }

    .user-info small {
        color: var(--text-secondary);
    }

    .nav-link {
        color: var(--text-secondary);
        padding: 12px 20px;
        margin: 5px 10px;
        border-radius: 10px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        position: relative;
        font-weight: 500;
    }

    .nav-link i {
        width: 20px;
        margin-left: 10px;
        color: var(--text-muted);
        transition: all 0.3s ease;
    }

    .nav-link:hover,
    .nav-link.active {
        color: var(--primary);
        background: linear-gradient(135deg, rgba(255, 140, 66, 0.1) 0%, rgba(255, 107, 53, 0.05) 100%);
        transform: translateX(-3px);
    }

    .nav-link:hover i,
    .nav-link.active i {
        color: var(--primary);
    }

    .nav-link.text-danger {
        color: #dc3545 !important;
    }

    .nav-link.text-danger:hover {
        background: rgba(220, 53, 69, 0.1);
        transform: translateX(-3px);
    }

    .glass-card {
        background: rgba(255, 140, 66, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: var(--border-radius);
    }

    .glass-card h6 {
        color: var(--text-primary);
        font-weight: 600;
    }

    .glass-card small {
        color: var(--text-secondary);
    }

    .mobile-menu-toggle {
        display: none;
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1001;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border: none;
        border-radius: 10px;
        width: 45px;
        height: 45px;
        color: white;
        font-size: 1.2rem;
        box-shadow: 0 4px 15px rgba(255, 140, 66, 0.4);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .mobile-menu-toggle:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(255, 140, 66, 0.5);
    }

    .mobile-menu-toggle:active {
        transform: scale(0.95);
    }

    /* Main Content Margin */
    .main-content {
        margin-right: 260px;
        margin-left: 0;
        padding: 30px;
        transition: all 0.3s ease;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .sidebar {
            width: 70px;
        }

        .sidebar-text {
            display: none;
        }

        .sidebar:hover {
            width: 260px;
        }

        .sidebar:hover .sidebar-text {
            display: inline;
        }

        .main-content {
            margin-right: 70px;
            margin-left: 0;
        }

        .mobile-menu-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Smaller logo on mobile */
        .logo i {
            font-size: 1.8rem !important;
        }

        .logo h4 {
            font-size: 1.1rem !important;
        }
    }

    @media (max-width: 576px) {
        .sidebar {
            transform: translateX(100%);
            width: 260px;
        }

        .sidebar.show {
            transform: translateX(0);
        }

        .sidebar.show .sidebar-text {
            display: inline;
        }

        .main-content {
            margin-right: 0;
            margin-left: 0;
        }

        /* Even smaller logo on very small screens */
        .logo i {
            font-size: 1.5rem !important;
        }

        .logo h4 {
            font-size: 0.95rem !important;
        }

        .dashboard-header h2 {
            font-size: 1.5rem !important;
        }
    }

    /* Sidebar Overlay */
    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .sidebar-overlay.show {
        opacity: 1;
        visibility: visible;
    }

    /* Scrollbar */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: rgba(255, 140, 66, 0.05);
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: var(--primary);
        border-radius: 3px;
    }
</style>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('show');
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function (e) {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.querySelector('.mobile-menu-toggle');

        if (window.innerWidth <= 576 &&
            sidebar && toggle &&
            !sidebar.contains(e.target) &&
            !toggle.contains(e.target)) {
            sidebar.classList.remove('show');
        }
    });
</script>