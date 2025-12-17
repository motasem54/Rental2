<?php
// dashboard.php
session_start();
require_once 'config/database.php';
require_once 'core/Auth.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get user info safely
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'customer';
$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'مستخدم';

$pageTitle = 'لوحة التحكم - نظام تأجير السيارات';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<style>
    /* Dashboard Specific Styles */
    .dashboard-header {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 4px 20px rgba(255, 140, 66, 0.1);
    }

    .dashboard-header h2 {
        color: var(--text-primary);
        font-weight: 700;
        margin-bottom: 5px;
    }

    .dashboard-header small {
        color: var(--text-secondary);
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(255, 140, 66, 0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--primary-dark));
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(255, 140, 66, 0.15);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: white;
        margin-bottom: 15px;
        box-shadow: 0 4px 15px rgba(255, 140, 66, 0.3);
    }

    .stat-icon.orange {
        background: linear-gradient(135deg, #FF8C42 0%, #FF6B35 100%);
    }

    .stat-icon.green {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .stat-icon.blue {
        background: linear-gradient(135deg, #17a2b8 0%, #0dcaf0 100%);
    }

    .stat-icon.yellow {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.95rem;
        color: var(--text-secondary);
        margin-bottom: 10px;
    }

    .stat-change {
        font-size: 0.85rem;
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
    }

    .stat-change.positive {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    .stat-change.negative {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    /*Charts Section */
    .charts-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }

    .chart-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(255, 140, 66, 0.1);
    }

    .chart-card h5 {
        color: var(--text-primary);
        font-weight: 600;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chart-card h5 i {
        color: var(--primary);
    }

    /* Quick Actions */
    .quick-actions {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(255, 140, 66, 0.1);
        margin-bottom: 30px;
    }

    .quick-actions h5 {
        color: var(--text-primary);
        font-weight: 600;
        margin-bottom: 20px;
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        background: rgba(255, 140, 66, 0.05);
        border: 2px solid var(--glass-border);
        border-radius: 12px;
        color: var(--text-primary);
        transition: all 0.3s ease;
        margin-bottom: 10px;
        cursor: pointer;
        text-decoration: none;
    }

    .action-btn:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        transform: translateX(-5px);
    }

    .action-btn i {
        font-size: 1.2rem;
        color: var(--primary);
        transition: all 0.3s ease;
    }

    .action-btn:hover i {
        color: white;
    }

    @media (max-width: 992px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Main Content -->
<div class="main-content">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>لوحة التحكم</h2>
                <small>مرحباً بك <?php echo htmlspecialchars($full_name); ?></small>
            </div>
            <div>
                <button class="btn btn-orange">
                    <i class="fas fa-plus me-2"></i>
                    إضافة جديد
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-car"></i>
            </div>
            <div class="stat-number" id="totalCars">0</div>
            <div class="stat-label">إجمالي السيارات</div>
            <span class="stat-change positive">+0 هذا الشهر</span>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-handshake"></i>
            </div>
            <div class="stat-number" id="activeRentals">0</div>
            <div class="stat-label">حجوزات نشطة</div>
            <span class="stat-change positive">+0 اليوم</span>
        </div>

        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-shekel-sign"></i>
            </div>
            <div class="stat-number" id="monthlyIncome">0 ₪</div>
            <div class="stat-label">الدخل الشهري</div>
            <span class="stat-change positive">+0%</span>
        </div>

        <div class="stat-card">
            <div class="stat-icon yellow">
                <i class="fas fa-tools"></i>
            </div>
            <div class="stat-number" id="maintenanceDue">0</div>
            <div class="stat-label">سيارات تحتاج صيانة</div>
            <span class="stat-change negative">تحتاج متابعة</span>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
        <div class="chart-card">
            <h5>
                <i class="fas fa-chart-line"></i>
                الإيرادات الشهرية
            </h5>
            <div style="height: 300px; position: relative;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h5>
                <i class="fas fa-chart-pie"></i>
                توزيع الحجوزات
            </h5>
            <div style="height: 300px; position: relative;">
                <canvas id="bookingChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h5><i class="fas fa-bolt me-2"></i>إجراءات سريعة</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <a href="modules/cars/add.php" class="action-btn">
                    <i class="fas fa-car"></i>
                    <span>إضافة سيارة جديدة</span>
                </a>
            </div>
            <div class="col-md-6 mb-3">
                <a href="modules/bookings/create.php" class="action-btn">
                    <i class="fas fa-calendar-plus"></i>
                    <span>حجز جديد</span>
                </a>
            </div>
            <div class="col-md-6 mb-3">
                <a href="modules/customers/add.php" class="action-btn">
                    <i class="fas fa-user-plus"></i>
                    <span>إضافة عميل جديد</span>
                </a>
            </div>
            <div class="col-md-6 mb-3">
                <a href="modules/reports/financial.php" class="action-btn">
                    <i class="fas fa-file-invoice"></i>
                    <span>عرض التقارير</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script>
    // Initialize Dashboard
    document.addEventListener('DOMContentLoaded', function () {
        loadDashboardStats();
        initCharts();

        // Auto-refresh every 60 seconds
        setInterval(loadDashboardStats, 60000);
    });

    // Load Dashboard Statistics
    async function loadDashboardStats() {
        try {
            // For now, show sample data
            document.getElementById('totalCars').textContent = '12';
            document.getElementById('activeRentals').textContent = '8';
            document.getElementById('monthlyIncome').textContent = '45,000 ₪';
            document.getElementById('maintenanceDue').textContent = '3';
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }

    // Initialize Charts
    function initCharts() {
        // Helper function to format large numbers
        function formatLargeNumber(num) {
            if (num >= 1000000) {
                return (num / 1000000).toFixed(1) + 'M';
            }
            if (num >= 1000) {
                return (num / 1000).toFixed(1) + 'K';
            }
            return num.toString();
        }

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                datasets: [{
                    label: 'الإيرادات (₪)',
                    data: [30000, 35000, 32000, 40000, 38000, 45000],
                    borderColor: '#FF8C42',
                    backgroundColor: 'rgba(255, 140, 66, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return 'الإيرادات: ' + context.parsed.y.toLocaleString('ar-EG') + ' ₪';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return formatLargeNumber(value) + ' ₪';
                            }
                        }
                    }
                }
            }
        });

        // Booking Distribution Chart
        const bookingCtx = document.getElementById('bookingChart').getContext('2d');
        new Chart(bookingCtx, {
            type: 'doughnut',
            data: {
                labels: ['نشطة', 'مكتملة', 'ملغية', 'قيد الانتظار'],
                datasets: [{
                    data: [8, 15, 2, 5],
                    backgroundColor: [
                        '#28a745',
                        '#FF8C42',
                        '#dc3545',
                        '#ffc107'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>

.glass-card {
background: var(--glass);
backdrop-filter: blur(20px);
border: 1px solid var(--glass-border);
border-radius: 15px;
padding: 20px;
margin-bottom: 20px;
transition: all 0.3s ease;
}

.glass-card:hover {
transform: translateY(-5px);
box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
border-color: var(--primary);
}

.stat-card {
text-align: center;
padding: 25px 15px;
}

.stat-card i {
font-size: 2.5rem;
margin-bottom: 15px;
}

.stat-card .number {
font-size: 2rem;
font-weight: bold;
margin-bottom: 5px;
}

.stat-card .label {
font-size: 0.9rem;
opacity: 0.8;
}

.card-icon {
width: 60px;
height: 60px;
border-radius: 12px;
display: flex;
align-items: center;
justify-content: center;
margin-bottom: 15px;
}

.bg-primary-gradient {
background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
}

.bg-success-gradient {
background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.bg-info-gradient {
background: linear-gradient(135deg, #17a2b8 0%, #0dcaf0 100%);
}

.bg-warning-gradient {
background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
}

.btn-orange {
background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
border: none;
color: white;
padding: 10px 25px;
border-radius: 50px;
font-weight: 600;
transition: all 0.3s ease;
}

.btn-orange:hover {
transform: translateY(-2px);
box-shadow: 0 8px 20px rgba(255, 87, 34, 0.3);
}

.sidebar {
background: rgba(18, 18, 18, 0.95);
backdrop-filter: blur(20px);
border-left: 1px solid var(--glass-border);
height: 100vh;
position: fixed;
left: 0;
top: 0;
width: 250px;
padding-top: 20px;
}

.main-content {
margin-right: 250px;
padding: 20px;
}

@media (max-width: 768px) {
.sidebar {
width: 70px;
}

.main-content {
margin-right: 70px;
}

.sidebar-text {
display: none;
}
}

.nav-link {
color: #ccc;
padding: 12px 20px;
margin: 5px 10px;
border-radius: 10px;
transition: all 0.3s ease;
}

.nav-link:hover, .nav-link.active {
color: white;
background: rgba(255, 87, 34, 0.2);
border-right: 3px solid var(--primary);
}

.nav-link i {
width: 20px;
margin-left: 10px;
}

.logo {
padding: 20px;
text-align: center;
border-bottom: 1px solid var(--glass-border);
margin-bottom: 20px;
}

.logo img {
max-width: 120px;
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
}

.notification-badge {
position: absolute;
top: 8px;
right: 8px;
background: var(--primary);
color: white;
border-radius: 50%;
width: 20px;
height: 20px;
font-size: 0.7rem;
display: flex;
align-items: center;
justify-content: center;
}

.realtime-update {
animation: pulse 2s infinite;
}

@keyframes pulse {
0% { opacity: 1; }
50% { opacity: 0.7; }
100% { opacity: 1; }
}
</style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-car fa-2x text-orange"></i>
            <h4 class="mt-2 sidebar-text">تأجير السيارات</h4>
        </div>

        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h6 class="mb-1 sidebar-text"><?php echo $_SESSION['full_name']; ?></h6>
            <small
                class="text-muted sidebar-text"><?php echo $_SESSION['role'] == 'admin' ? 'مدير النظام' : 'عميل'; ?></small>
        </div>

        <nav class="nav flex-column">
            <a href="dashboard.php" class="nav-link active">
                <i class="fas fa-home"></i>
                <span class="sidebar-text">لوحة التحكم</span>
            </a>

            <a href="modules/cars/index.php" class="nav-link">
                <i class="fas fa-car"></i>
                <span class="sidebar-text">إدارة السيارات</span>
            </a>

            <a href="modules/customers/index.php" class="nav-link">
                <i class="fas fa-users"></i>
                <span class="sidebar-text">العملاء</span>
            </a>

            <a href="modules/bookings/index.php" class="nav-link">
                <i class="fas fa-calendar-check"></i>
                <span class="sidebar-text">الحجوزات</span>
                <span class="notification-badge" id="bookingNotifications">0</span>
            </a>

            <a href="modules/maintenance/index.php" class="nav-link">
                <i class="fas fa-tools"></i>
                <span class="sidebar-text">الصيانة</span>
                <span class="notification-badge" id="maintenanceNotifications">0</span>
            </a>

            <a href="modules/insurance/index.php" class="nav-link">
                <i class="fas fa-shield-alt"></i>
                <span class="sidebar-text">التأمين</span>
            </a>

            <a href="modules/reports/financial.php" class="nav-link">
                <i class="fas fa-chart-line"></i>
                <span class="sidebar-text">التقارير</span>
            </a>

            <?php if ($user_role == 'admin'): ?>
                <a href="modules/settings/index.php" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span class="sidebar-text">الإعدادات</span>
                </a>
            <?php endif; ?>

            <div class="mt-4">
                <a href="logout.php" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="sidebar-text">تسجيل الخروج</span>
                </a>
            </div>
        </nav>

        <!-- System Status -->
        <div class="glass-card m-3 mt-4">
            <h6 class="mb-2"><i class="fas fa-server me-2"></i><span class="sidebar-text">حالة النظام</span></h6>
            <div class="system-status">
                <div class="d-flex justify-content-between mb-1">
                    <small class="sidebar-text">الأداء</small>
                    <small id="systemPerformance">85%</small>
                </div>
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-success" id="performanceBar" style="width: 85%"></div>
                </div>

                <div class="d-flex justify-content-between mt-3 mb-1">
                    <small class="sidebar-text">التحديثات</small>
                    <small id="updatesCount">3</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">لوحة التحكم</h2>
                <small class="text-muted">مرحباً بك <?php echo $_SESSION['full_name']; ?></small>
            </div>

            <div class="d-flex align-items-center">
                <!-- WhatsApp Quick Button -->
                <button class="btn btn-success me-2" onclick="openWhatsAppModal()">
                    <i class="fab fa-whatsapp me-1"></i> إرسال واتساب
                </button>

                <!-- Notifications -->
                <div class="dropdown me-3">
                    <button class="btn btn-outline-light position-relative" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge" id="totalNotifications">5</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end glass-card p-3" style="min-width: 300px;">
                        <h6 class="mb-3">الإشعارات</h6>
                        <div id="notificationsList">
                            <!-- Notifications will be loaded via AJAX -->
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="d-none d-md-flex">
                    <small class="me-3">
                        <i class="fas fa-clock text-info me-1"></i>
                        <span id="currentTime">--:--</span>
                    </small>
                    <small>
                        <i class="fas fa-calendar text-success me-1"></i>
                        <span id="currentDate">--/--/----</span>
                    </small>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="glass-card stat-card">
                    <div class="card-icon bg-primary-gradient mx-auto">
                        <i class="fas fa-car"></i>
                    </div>
                    <div class="number" id="statTotalCars">0</div>
                    <div class="label">السيارات المتاحة</div>
                    <small class="text-success realtime-update" id="carsChange">+0 اليوم</small>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="glass-card stat-card">
                    <div class="card-icon bg-success-gradient mx-auto">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="number" id="statActiveRentals">0</div>
                    <div class="label">حجوزات نشطة</div>
                    <small class="text-info realtime-update" id="rentalsChange">+0 اليوم</small>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="glass-card stat-card">
                    <div class="card-icon bg-info-gradient mx-auto">
                        <i class="fas fa-shekel-sign"></i>
                    </div>
                    <div class="number" id="statMonthlyIncome">0 ₪</div>
                    <div class="label">الدخل الشهري</div>
                    <small class="text-warning realtime-update" id="incomeChange">+0% عن الشهر الماضي</small>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="glass-card stat-card">
                    <div class="card-icon bg-warning-gradient mx-auto">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="number" id="statMaintenanceDue">0</div>
                    <div class="label">سيارات تحتاج صيانة</div>
                    <small class="text-danger realtime-update" id="maintenanceAlert">⚠️ قريباً</small>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="glass-card">
                    <h5 class="mb-3"><i class="fas fa-chart-line me-2"></i> الإيرادات الشهرية</h5>
                    <canvas id="revenueChart" height="250"></canvas>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="glass-card h-100">
                    <h5 class="mb-3"><i class="fas fa-chart-pie me-2"></i> توزيع الحجوزات</h5>
                    <canvas id="bookingDistributionChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity & Quick Actions -->
        <div class="row">
            <div class="col-lg-8">
                <div class="glass-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i> النشاطات الأخيرة</h5>
                        <button class="btn btn-sm btn-outline-light" onclick="loadActivityLog()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover" id="activityTable">
                            <thead>
                                <tr>
                                    <th>النشاط</th>
                                    <th>المستخدم</th>
                                    <th>الوقت</th>
                                    <th>التفاصيل</th>
                                </tr>
                            </thead>
                            <tbody id="activityBody">
                                <!-- Activity logs will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="glass-card">
                    <h5 class="mb-3"><i class="fas fa-bolt me-2"></i> إجراءات سريعة</h5>

                    <div class="d-grid gap-2">
                        <button class="btn btn-orange mb-2" onclick="quickAction('add_car')">
                            <i class="fas fa-plus me-2"></i> إضافة سيارة جديدة
                        </button>

                        <button class="btn btn-outline-light mb-2" onclick="quickAction('new_booking')">
                            <i class="fas fa-calendar-plus me-2"></i> حجز جديد
                        </button>

                        <button class="btn btn-outline-light mb-2" onclick="quickAction('send_bulk_whatsapp')">
                            <i class="fab fa-whatsapp me-2"></i> إرسال إشعار جماعي
                        </button>

                        <button class="btn btn-outline-light mb-2" onclick="quickAction('generate_report')">
                            <i class="fas fa-file-pdf me-2"></i> إنشاء تقرير
                        </button>

                        <button class="btn btn-outline-light mb-2" onclick="quickAction('backup')">
                            <i class="fas fa-database me-2"></i> نسخ احتياطي
                        </button>

                        <?php if ($user_role == 'admin'): ?>
                            <button class="btn btn-outline-info" onclick="quickAction('system_settings')">
                                <i class="fas fa-cog me-2"></i> إعدادات النظام
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- System Info -->
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i> معلومات النظام</h6>
                        <small class="d-block mb-1">
                            <i class="fas fa-code me-2"></i>
                            الإصدار: 2.0.0
                        </small>
                        <small class="d-block mb-1">
                            <i class="fas fa-database me-2"></i>
                            قاعدة البيانات: <span id="dbSize">0 MB</span>
                        </small>
                        <small class="d-block">
                            <i class="fas fa-clock me-2"></i>
                            وقت التشغيل: <span id="uptime">0 يوم</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="glass-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i> آخر الحجوزات</h5>
                        <a href="modules/bookings/index.php" class="btn btn-sm btn-outline-light">
                            عرض الكل <i class="fas fa-arrow-left"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover" id="recentBookingsTable">
                            <thead>
                                <tr>
                                    <th>رقم الحجز</th>
                                    <th>العميل</th>
                                    <th>السيارة</th>
                                    <th>الفترة</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="recentBookingsBody">
                                <!-- Recent bookings will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- WhatsApp Modal -->
    <div class="modal fade" id="whatsappModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content glass-card">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fab fa-whatsapp text-success me-2"></i> إرسال رسالة واتساب</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="whatsappForm" class="ajax-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">المستلم</label>
                                    <select class="form-select glass-input" id="whatsappRecipient" name="recipient">
                                        <option value="">اختر المستلم</option>
                                        <option value="all_customers">جميع العملاء</option>
                                        <option value="active_rentals">العملاء بحجوزات نشطة</option>
                                        <option value="specific">مستمع محدد</option>
                                    </select>
                                </div>

                                <div class="mb-3" id="specificCustomerField" style="display: none;">
                                    <label class="form-label">اختر عميل</label>
                                    <select class="form-select glass-input" id="specificCustomer" name="customer_id">
                                        <!-- Customers will be loaded via AJAX -->
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">نوع الرسالة</label>
                                    <select class="form-select glass-input" id="messageType" name="message_type">
                                        <option value="custom">رسالة مخصصة</option>
                                        <option value="booking_confirmation">تأكيد حجز</option>
                                        <option value="payment_reminder">تذكير بدفع</option>
                                        <option value="maintenance_alert">تنبيه صيانة</option>
                                        <option value="promotional">رسالة ترويجية</option>
                                    </select>
                                </div>

                                <div class="mb-3" id="templateField" style="display: none;">
                                    <label class="form-label">اختر قالب</label>
                                    <select class="form-select glass-input" id="whatsappTemplate" name="template">
                                        <!-- Templates will be loaded via AJAX -->
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" id="customMessageField">
                            <label class="form-label">الرسالة</label>
                            <textarea class="form-control glass-input" id="customMessage" name="message" rows="4"
                                placeholder="اكتب رسالتك هنا..."></textarea>
                            <small class="text-muted">الحد الأقصى 1000 حرف</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="text" class="form-control glass-input" id="phoneNumber" name="phone"
                                placeholder="+972599999999">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="fab fa-whatsapp me-2"></i> إرسال الرسالة
                            </button>
                        </div>
                    </form>

                    <div id="whatsappResponse" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script src="assets/js/ajax-handler.js"></script>
    <script src="assets/js/realtime.js"></script>

    <script>
        // Initialize Dashboard
        document.addEventListener('DOMContentLoaded', function () {
            loadDashboardStats();
            loadRecentActivity();
            loadRecentBookings();
            loadNotifications();
            initCharts();
            updateDateTime();

            // Real-time updates every 30 seconds
            setInterval(loadDashboardStats, 30000);
            setInterval(loadNotifications, 60000);
            setInterval(updateDateTime, 1000);
        });

        // Load Dashboard Statistics
        async function loadDashboardStats() {
            try {
                const response = await fetch('api/dashboard.php?action=get_stats');
                const data = await response.json();

                if (data.success) {
                    // Update stats cards
                    document.getElementById('statTotalCars').textContent = data.stats.total_cars;
                    document.getElementById('statActiveRentals').textContent = data.stats.active_rentals;
                    document.getElementById('statMonthlyIncome').textContent = data.stats.monthly_income + ' ₪';
                    document.getElementById('statMaintenanceDue').textContent = data.stats.maintenance_due;

                    // Update changes
                    document.getElementById('carsChange').textContent =
                        data.stats.cars_today > 0 ? `+${data.stats.cars_today} اليوم` : 'لا تغيير';

                    document.getElementById('rentalsChange').textContent =
                        data.stats.rentals_today > 0 ? `+${data.stats.rentals_today} اليوم` : 'لا تغيير';

                    document.getElementById('incomeChange').textContent =
                        data.stats.income_change > 0 ? `+${data.stats.income_change}% عن الشهر الماضي` :
                            data.stats.income_change < 0 ? `${data.stats.income_change}% عن الشهر الماضي` : 'لا تغيير';

                    // Update notifications
                    document.getElementById('bookingNotifications').textContent = data.stats.pending_bookings;
                    document.getElementById('maintenanceNotifications').textContent = data.stats.maintenance_due;
                    document.getElementById('totalNotifications').textContent =
                        data.stats.pending_bookings + data.stats.maintenance_due;

                    // System info
                    document.getElementById('dbSize').textContent = data.stats.db_size + ' MB';
                    document.getElementById('uptime').textContent = data.stats.uptime + ' يوم';
                    document.getElementById('systemPerformance').textContent = data.stats.performance + '%';
                    document.getElementById('performanceBar').style.width = data.stats.performance + '%';
                    document.getElementById('updatesCount').textContent = data.stats.updates_count;

                    // Update charts if they exist
                    if (window.revenueChart && window.bookingDistributionChart) {
                        updateCharts(data.charts);
                    }
                }
            } catch (error) {
                console.error('Error loading dashboard stats:', error);
            }
        }

        // Load Recent Activity
        async function loadRecentActivity() {
            try {
                const response = await fetch('api/activity.php?action=recent');
                const data = await response.json();

                if (data.success) {
                    let html = '';
                    data.activities.forEach(activity => {
                        html += `
                    <tr>
                        <td>
                            <i class="fas fa-${activity.icon} me-2 text-${activity.color}"></i>
                            ${activity.action}
                        </td>
                        <td>${activity.user}</td>
                        <td>${activity.time}</td>
                        <td><small class="text-muted">${activity.details}</small></td>
                    </tr>`;
                    });

                    document.getElementById('activityBody').innerHTML = html;
                }
            } catch (error) {
                console.error('Error loading activity:', error);
            }
        }

        // Load Recent Bookings
        async function loadRecentBookings() {
            try {
                const response = await fetch('api/bookings.php?action=recent');
                const data = await response.json();

                if (data.success) {
                    let html = '';
                    data.bookings.forEach(booking => {
                        let statusBadge = '';
                        switch (booking.status) {
                            case 'active':
                                statusBadge = '<span class="badge bg-success">نشط</span>';
                                break;
                            case 'pending':
                                statusBadge = '<span class="badge bg-warning">قيد الانتظار</span>';
                                break;
                            case 'completed':
                                statusBadge = '<span class="badge bg-info">مكتمل</span>';
                                break;
                            case 'cancelled':
                                statusBadge = '<span class="badge bg-danger">ملغي</span>';
                                break;
                        }

                        html += `
                    <tr>
                        <td>#${booking.id}</td>
                        <td>${booking.customer_name}</td>
                        <td>${booking.car_name}</td>
                        <td>${booking.start_date} إلى ${booking.end_date}</td>
                        <td>${booking.total} ₪</td>
                        <td>${statusBadge}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-light" onclick="viewBooking(${booking.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick="sendWhatsAppReminder(${booking.id})">
                                <i class="fab fa-whatsapp"></i>
                            </button>
                        </td>
                    </tr>`;
                    });

                    document.getElementById('recentBookingsBody').innerHTML = html;
                }
            } catch (error) {
                console.error('Error loading recent bookings:', error);
            }
        }

        // Load Notifications
        async function loadNotifications() {
            try {
                const response = await fetch('api/notifications.php?action=get');
                const data = await response.json();

                if (data.success) {
                    let html = '';
                    data.notifications.forEach(notification => {
                        let icon = 'bell';
                        let color = 'primary';

                        if (notification.type === 'booking') {
                            icon = 'calendar-check';
                            color = 'success';
                        } else if (notification.type === 'maintenance') {
                            icon = 'tools';
                            color = 'warning';
                        } else if (notification.type === 'payment') {
                            icon = 'money-bill-wave';
                            color = 'info';
                        }

                        html += `
                    <div class="notification-item mb-2 p-2 border-bottom border-secondary">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-${icon} text-${color} mt-1 me-2"></i>
                            <div>
                                <small class="d-block">${notification.title}</small>
                                <small class="text-muted">${notification.message}</small>
                                <small class="d-block text-end">${notification.time}</small>
                            </div>
                        </div>
                    </div>`;
                    });

                    document.getElementById('notificationsList').innerHTML = html;
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
            }
        }

        // Initialize Charts
        function initCharts() {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            window.revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                    datasets: [{
                        label: 'الإيرادات',
                        data: [12000, 19000, 15000, 25000, 22000, 30000],
                        borderColor: '#FF5722',
                        backgroundColor: 'rgba(255, 87, 34, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: 'white'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: 'white',
                                callback: function (value) {
                                    return value + ' ₪';
                                }
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: 'white'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        }
                    }
                }
            });

            // Booking Distribution Chart
            const distributionCtx = document.getElementById('bookingDistributionChart').getContext('2d');
            window.bookingDistributionChart = new Chart(distributionCtx, {
                type: 'doughnut',
                data: {
                    labels: ['نشط', 'مكتمل', 'قيد الانتظار', 'ملغي'],
                    datasets: [{
                        data: [12, 19, 3, 2],
                        backgroundColor: [
                            '#28a745',
                            '#17a2b8',
                            '#ffc107',
                            '#dc3545'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: 'white',
                                padding: 20
                            }
                        }
                    }
                }
            });
        }

        // Update Charts with new data
        function updateCharts(chartData) {
            if (chartData.revenue && window.revenueChart) {
                window.revenueChart.data.datasets[0].data = chartData.revenue;
                window.revenueChart.update();
            }

            if (chartData.distribution && window.bookingDistributionChart) {
                window.bookingDistributionChart.data.datasets[0].data = chartData.distribution;
                window.bookingDistributionChart.update();
            }
        }

        // Update Date and Time
        function updateDateTime() {
            const now = new Date();

            // Format time
            const time = now.toLocaleTimeString('ar-EG', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            // Format date
            const date = now.toLocaleDateString('ar-EG', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            document.getElementById('currentTime').textContent = time;
            document.getElementById('currentDate').textContent = date;
        }

        // Quick Actions
        function quickAction(action) {
            switch (action) {
                case 'add_car':
                    window.location.href = 'modules/cars/add.php';
                    break;
                case 'new_booking':
                    window.location.href = 'modules/bookings/create.php';
                    break;
                case 'send_bulk_whatsapp':
                    openWhatsAppModal();
                    break;
                case 'generate_report':
                    window.location.href = 'modules/reports/financial.php';
                    break;
                case 'backup':
                    createBackup();
                    break;
                case 'system_settings':
                    window.location.href = 'modules/settings/index.php';
                    break;
            }
        }
    