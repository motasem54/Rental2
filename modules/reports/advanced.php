<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

// التحقق من الصلاحية
if (!$auth->hasPermission('view_reports')) {
    header('Location: ../../dashboard.php');
    exit();
}

$db = Database::getInstance()->getConnection();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التقارير المتقدمة - نظام تأجير السيارات</title>

    <link rel="stylesheet" href="../../assets/css/glassmorphism.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    <style>
        .report-card {
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            border-color: var(--primary);
        }

        .report-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        .report-summary {
            background: linear-gradient(135deg, rgba(255, 87, 34, 0.1) 0%, rgba(18, 18, 18, 0.1) 100%);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .summary-item {
            text-align: center;
            padding: 15px;
        }

        .summary-item .number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .summary-item .label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }

        .filter-panel {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .report-actions {
            position: sticky;
            top: 20px;
            z-index: 100;
        }

        .comparison-card {
            border-left: 4px solid;
            padding: 15px;
            margin-bottom: 15px;
        }

        .comparison-up {
            border-left-color: #28a745;
        }

        .comparison-down {
            border-left-color: #dc3545;
        }

        .comparison-equal {
            border-left-color: #6c757d;
        }
    </style>
</head>

<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-chart-line me-2"></i> التقارير المتقدمة</h2>
                    <small class="text-muted">تحليلات وإحصائيات مفصلة عن نشاط الشركة</small>
                </div>

                <div>
                    <button class="btn btn-orange me-2" onclick="generateCustomReport()">
                        <i class="fas fa-plus me-2"></i> تقرير مخصص
                    </button>

                    <button class="btn btn-outline-light" onclick="exportAllReports()">
                        <i class="fas fa-download me-2"></i> تصدير جميع التقارير
                    </button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="report-summary">
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="summary-item">
                            <div class="number text-primary" id="totalRevenue">0 ₪</div>
                            <div class="label">إجمالي الإيرادات</div>
                            <small class="text-success" id="revenueChange">+0% عن الشهر الماضي</small>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="summary-item">
                            <div class="number text-success" id="activeRentals">0</div>
                            <div class="label">عمليات تأجير نشطة</div>
                            <small class="text-info" id="rentalsChange">+0 عن الأسبوع الماضي</small>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="summary-item">
                            <div class="number text-warning" id="availableCars">0</div>
                            <div class="label">سيارات متاحة</div>
                            <small class="text-muted" id="utilizationRate">نسبة الاستخدام: 0%</small>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="summary-item">
                            <div class="number text-info" id="totalCustomers">0</div>
                            <div class="label">إجمالي العملاء</div>
                            <small class="text-success" id="customersChange">+0 عميل جديد</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="filter-panel">
                <h5 class="mb-3"><i class="fas fa-filter me-2"></i> عوامل التصفية</h5>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">نوع التقرير</label>
                        <select class="form-select glass-input" id="reportType">
                            <option value="financial">تقارير مالية</option>
                            <option value="rentals">تقارير التأجير</option>
                            <option value="customers">تقارير العملاء</option>
                            <option value="cars">تقارير السيارات</option>
                            <option value="maintenance">تقارير الصيانة</option>
                            <option value="performance">تقارير الأداء</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">الفترة الزمنية</label>
                        <select class="form-select glass-input" id="timePeriod">
                            <option value="today">اليوم</option>
                            <option value="yesterday">أمس</option>
                            <option value="week">هذا الأسبوع</option>
                            <option value="month" selected>هذا الشهر</option>
                            <option value="quarter">هذا الربع</option>
                            <option value="year">هذه السنة</option>
                            <option value="custom">مخصص</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" class="form-control glass-input" id="dateFrom"
                            value="<?php echo date('Y-m-01'); ?>">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" class="form-control glass-input" id="dateTo"
                            value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-orange w-100" onclick="loadReports()">
                            <i class="fas fa-sync-alt me-2"></i> تطبيق
                        </button>
                    </div>
                </div>

                <!-- Advanced Filters -->
                <div class="row mt-3" id="advancedFilters" style="display: none;">
                    <div class="col-md-3">
                        <label class="form-label">تصنيف السيارات</label>
                        <select class="form-select glass-input" id="carCategory">
                            <option value="">جميع التصنيفات</option>
                            <option value="economy">اقتصادية</option>
                            <option value="mid">متوسطة</option>
                            <option value="luxury">فاخرة</option>
                            <option value="suv">دفع رباعي</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">حالة العملية</label>
                        <select class="form-select glass-input" id="rentalStatus">
                            <option value="">جميع الحالات</option>
                            <option value="active">نشطة</option>
                            <option value="completed">مكتملة</option>
                            <option value="cancelled">ملغية</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">نوع العميل</label>
                        <select class="form-select glass-input" id="customerType">
                            <option value="">جميع الأنواع</option>
                            <option value="individual">أفراد</option>
                            <option value="company">شركات</option>
                            <option value="regular">دائم</option>
                            <option value="new">جديد</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">ترتيب النتائج</label>
                        <select class="form-select glass-input" id="sortBy">
                            <option value="date">التاريخ</option>
                            <option value="revenue">الإيرادات</option>
                            <option value="count">العدد</option>
                            <option value="profit">الربح</option>
                        </select>
                    </div>
                </div>

                <div class="text-end mt-2">
                    <a href="#" class="text-orange text-decoration-none" onclick="toggleAdvancedFilters()">
                        <i class="fas fa-sliders-h me-1"></i> فلاتر متقدمة
                    </a>
                </div>
            </div>

            <!-- Reports Grid -->
            <div class="row" id="reportsGrid">
                <!-- Financial Reports -->
                <div class="col-xl-4 col-md-6 mb-4" data-category="financial">
                    <div class="report-card">
                        <div class="report-icon bg-primary-gradient">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h5>تقرير الإيرادات الشهري</h5>
                        <p class="text-muted">تحليل الإيرادات والمصروفات والأرباح</p>
                        <div class="chart-container">
                            <canvas id="revenueChart"></canvas>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <button class="btn btn-sm btn-outline-light" onclick="viewReport('revenue_monthly')">
                                <i class="fas fa-eye me-1"></i> عرض
                            </button>
                            <button class="btn btn-sm btn-outline-info"
                                onclick="exportReport('revenue_monthly', 'pdf')">
                                <i class="fas fa-download me-1"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Rental Reports -->
                <div class="col-xl-4 col-md-6 mb-4" data-category="rentals">
                    <div class="report-card">
                        <div class="report-icon bg-success-gradient">
                            <i class="fas fa-car"></i>
                        </div>
                        <h5>تقرير نشاط التأجير</h5>
                        <p class="text-muted">إحصائيات عمليات التأجير والحجوزات</p>
                        <div class="chart-container">
                            <canvas id="rentalsChart"></canvas>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <button class="btn btn-sm btn-outline-light" onclick="viewReport('rental_activity')">
                                <i class="fas fa-eye me-1"></i> عرض
                            </button>
                            <button class="btn btn-sm btn-outline-info"
                                onclick="exportReport('rental_activity', 'excel')">
                                <i class="fas fa-download me-1"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Customer Reports -->
                <div class="col-xl-4 col-md-6 mb-4" data-category="customers">
                    <div class="report-card">
                        <div class="report-icon bg-info-gradient">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5>تقرير تحليل العملاء</h5>
                        <p class="text-muted">توزيع العملاء وأدائهم وتكرار التعامل</p>
                        <div class="chart-container">
                            <canvas id="customersChart"></canvas>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <button class="btn btn-sm btn-outline-light" onclick="viewReport('customer_analysis')">
                                <i class="fas fa-eye me-1"></i> عرض
                            </button>
                            <button class="btn btn-sm btn-outline-info"
                                onclick="exportReport('customer_analysis', 'pdf')">
                                <i class="fas fa-download me-1"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Car Reports -->
                <div class="col-xl-4 col-md-6 mb-4" data-category="cars">
                    <div class="report-card">
                        <div class="report-icon bg-warning-gradient">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h5>تقرير أداء السيارات</h5>
                        <p class="text-muted">ربحية وأداء كل سيارة في الأسطول</p>
                        <div class="chart-container">
                            <canvas id="carsChart"></canvas>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <button class="btn btn-sm btn-outline-light" onclick="viewReport('car_performance')">
                                <i class="fas fa-eye me-1"></i> عرض
                            </button>
                            <button class="btn btn-sm btn-outline-info"
                                onclick="exportReport('car_performance', 'excel')">
                                <i class="fas fa-download me-1"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Reports -->
                <div class="col-xl-4 col-md-6 mb-4" data-category="maintenance">
                    <div class="report-card">
                        <div class="report-icon bg-danger-gradient">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h5>تقرير تكاليف الصيانة</h5>
                        <p class="text-muted">تحليل تكاليف الصيانة والإصلاحات</p>
                        <div class="chart-container">
                            <canvas id="maintenanceChart"></canvas>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <button class="btn btn-sm btn-outline-light" onclick="viewReport('maintenance_costs')">
                                <i class="fas fa-eye me-1"></i> عرض
                            </button>
                            <button class="btn btn-sm btn-outline-info"
                                onclick="exportReport('maintenance_costs', 'pdf')">
                                <i class="fas fa-download me-1"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Performance Reports -->
                <div class="col-xl-4 col-md-6 mb-4" data-category="performance">
                    <div class="report-card">
                        <div class="report-icon bg-secondary-gradient">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <h5>تقرير مؤشرات الأداء</h5>
                        <p class="text-muted">KPI ومقاييس أداء الشركة</p>
                        <div class="chart-container">
                            <canvas id="performanceChart"></canvas>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <button class="btn btn-sm btn-outline-light" onclick="viewReport('performance_indicators')">
                                <i class="fas fa-eye me-1"></i> عرض
                            </button>
                            <button class="btn btn-sm btn-outline-info"
                                onclick="exportReport('performance_indicators', 'excel')">
                                <i class="fas fa-download me-1"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Report View -->
            <div class="glass-card mt-4" id="detailedReportView" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0" id="reportTitle">تقرير مفصل</h4>
                    <div>
                        <button class="btn btn-sm btn-outline-light me-2" onclick="printReport()">
                            <i class="fas fa-print me-1"></i> طباعة
                        </button>
                        <button class="btn btn-sm btn-secondary" onclick="closeReport()">
                            <i class="fas fa-times me-1"></i> إغلاق
                        </button>
                    </div>
                </div>

                <div id="reportContent">
                    <!-- Report content will be loaded here -->
                </div>
            </div>

            <!-- Comparison Section -->
            <div class="glass-card mt-4">
                <h5 class="mb-3"><i class="fas fa-balance-scale me-2"></i> مقارنة الأداء</h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="comparison-card comparison-up">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6>معدل إشغال السيارات</h6>
                                    <small class="text-muted">هذا الشهر vs الشهر الماضي</small>
                                </div>
                                <div class="text-end">
                                    <h4 class="mb-0 text-success">85%</h4>
                                    <small>▲ 12% زيادة</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="comparison-card comparison-down">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6>متوسط فترة التأجير</h6>
                                    <small class="text-muted">هذا الشهر vs الشهر الماضي</small>
                                </div>
                                <div class="text-end">
                                    <h4 class="mb-0 text-danger">4.2 يوم</h4>
                                    <small>▼ 0.8 يوم انخفاض</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="comparison-card comparison-up">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6>رضا العملاء</h6>
                                    <small class="text-muted">هذا الشهر vs الشهر الماضي</small>
                                </div>
                                <div class="text-end">
                                    <h4 class="mb-0 text-success">94%</h4>
                                    <small>▲ 3% زيادة</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="comparison-card comparison-equal">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6>تكاليف الصيانة</h6>
                                    <small class="text-muted">هذا الشهر vs الشهر الماضي</small>
                                </div>
                                <div class="text-end">
                                    <h4 class="mb-0">15,200 ₪</h4>
                                    <small>▲ 2% زيادة</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scheduled Reports -->
            <div class="glass-card mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i> التقارير المجدولة</h5>
                    <button class="btn btn-sm btn-orange" onclick="scheduleNewReport()">
                        <i class="fas fa-plus me-1"></i> جدولة تقرير جديد
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark table-hover" id="scheduledReportsTable">
                        <thead>
                            <tr>
                                <th>اسم التقرير</th>
                                <th>النوع</th>
                                <th>التكرار</th>
                                <th>المرسل إليهم</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="scheduledReportsBody">
                            <!-- Scheduled reports will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Report Modal -->
    <div class="modal fade glass-modal" id="customReportModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> إنشاء تقرير مخصص</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="customReportForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">اسم التقرير</label>
                                    <input type="text" class="form-control glass-input" name="report_name" required
                                        placeholder="أدخل اسم التقرير">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">نوع التقرير</label>
                                    <select class="form-select glass-input" name="report_type" required>
                                        <option value="financial">تقرير مالي</option>
                                        <option value="rental">تقرير تأجير</option>
                                        <option value="customer">تقرير عملاء</option>
                                        <option value="car">تقرير سيارات</option>
                                        <option value="maintenance">تقرير صيانة</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">الحقول المطلوبة</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="revenue"
                                            id="fieldRevenue">
                                        <label class="form-check-label" for="fieldRevenue">الإيرادات</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="rentals"
                                            id="fieldRentals">
                                        <label class="form-check-label" for="fieldRentals">عمليات التأجير</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]"
                                            value="customers" id="fieldCustomers">
                                        <label class="form-check-label" for="fieldCustomers">العملاء</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="cars"
                                            id="fieldCars">
                                        <label class="form-check-label" for="fieldCars">السيارات</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="profit"
                                            id="fieldProfit">
                                        <label class="form-check-label" for="fieldProfit">الأرباح</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="expenses"
                                            id="fieldExpenses">
                                        <label class="form-check-label" for="fieldExpenses">المصروفات</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">التجميع</label>
                            <select class="form-select glass-input" name="group_by">
                                <option value="daily">يومي</option>
                                <option value="weekly">أسبوعي</option>
                                <option value="monthly" selected>شهري</option>
                                <option value="yearly">سنوي</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">تنسيق التصدير</label>
                            <select class="form-select glass-input" name="export_format">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                                <option value="html">HTML</option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-orange">
                                <i class="fas fa-magic me-2"></i> إنشاء التقرير
                            </button>
                        </div>
                    </form>

                    <div id="customReportResponse" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../assets/js/app.js"></script>
    <script src="../../assets/js/ajax-handler.js"></script>

    <script>
        // Initialize
        document.addEventListener('DOMContentLoaded', function () {
            loadSummaryStats();
            loadCharts();
            loadScheduledReports();

            // Set default dates
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

            document.getElementById('dateFrom').value = firstDay.toISOString().split('T')[0];
            document.getElementById('dateTo').value = today.toISOString().split('T')[0];
        });

        // Load Summary Statistics
        async function loadSummaryStats() {
            try {
                const response = await fetch('api/reports.php?action=get_summary');
                const data = await response.json();

                if (data.success) {
                    document.getElementById('totalRevenue').textContent = data.summary.total_revenue + ' ₪';
                    document.getElementById('activeRentals').textContent = data.summary.active_rentals;
                    document.getElementById('availableCars').textContent = data.summary.available_cars;
                    document.getElementById('totalCustomers').textContent = data.summary.total_customers;

                    document.getElementById('revenueChange').textContent =
                        data.summary.revenue_change > 0 ?
                            `+${data.summary.revenue_change}% عن الشهر الماضي` :
                            `${data.summary.revenue_change}% عن الشهر الماضي`;

                    document.getElementById('rentalsChange').textContent =
                        data.summary.rentals_change > 0 ?
                            `+${data.summary.rentals_change} عن الأسبوع الماضي` :
                            `${data.summary.rentals_change} عن الأسبوع الماضي`;

                    document.getElementById('utilizationRate').textContent =
                        `نسبة الاستخدام: ${data.summary.utilization_rate}%`;

                    document.getElementById('customersChange').textContent =
                        `+${data.summary.new_customers} عميل جديد`;
                }
            } catch (error) {
                console.error('Error loading summary stats:', error);
            }
        }

        // Load Charts
        function loadCharts() {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revenueCtx, {
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
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return value + ' ₪';
                                }
                            }
                        }
                    }
                }
            });

            // Rentals Chart
            const rentalsCtx = document.getElementById('rentalsChart').getContext('2d');
            new Chart(rentalsCtx, {
                type: 'bar',
                data: {
                    labels: ['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'],
                    datasets: [{
                        label: 'عمليات التأجير',
                        data: [12, 19, 8, 15, 10, 20, 14],
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Customers Chart
            const customersCtx = document.getElementById('customersChart').getContext('2d');
            new Chart(customersCtx, {
                type: 'doughnut',
                data: {
                    labels: ['أفراد', 'شركات', 'دائمين', 'جدد'],
                    datasets: [{
                        data: [45, 25, 15, 15],
                        backgroundColor: [
                            '#FF5722',
                            '#28a745',
                            '#17a2b8',
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

            // Cars Chart
            const carsCtx = document.getElementById('carsChart').getContext('2d');
            new Chart(carsCtx, {
                type: 'radar',
                data: {
                    labels: ['التوفر', 'الإيرادات', 'الصيانة', 'الرضا', 'الكلفة'],
                    datasets: [{
                        label: 'أداء السيارات',
                        data: [85, 92, 78, 88, 75],
                        backgroundColor: 'rgba(255, 193, 7, 0.2)',
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });

            // Maintenance Chart
            const maintenanceCtx = document.getElementById('maintenanceChart').getContext('2d');
            new Chart(maintenanceCtx, {
                type: 'pie',
                data: {
                    labels: ['صيانة دورية', 'إصلاحات', 'حوادث', 'قطع غيار'],
                    datasets: [{
                        data: [40, 25, 20, 15],
                        backgroundColor: [
                            '#dc3545',
                            '#fd7e14',
                            '#6f42c1',
                            '#20c997'
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

            // Performance Chart
            const performanceCtx = document.getElementById('performanceChart').getContext('2d');
            new Chart(performanceCtx, {
                type: 'bar',
                data: {
                    labels: ['معدل الإشغال', 'رضا العملاء', 'نمو الإيرادات', 'كفاءة الصيانة'],
                    datasets: [{
                        label: 'المؤشر الحالي',
                        data: [85, 92, 78, 88],
                        backgroundColor: 'rgba(108, 117, 125, 0.7)'
                    }, {
                        label: 'الهدف',
                        data: [90, 95, 85, 90],
                        backgroundColor: 'rgba(255, 87, 34, 0.7)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }

        // Load Reports based on filters
        async function loadReports() {
            const reportType = document.getElementById('reportType').value;
            const timePeriod = document.getElementById('timePeriod').value;
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;

            // Filter reports grid
            const reports = document.querySelectorAll('#reportsGrid [data-category]');
            reports.forEach(report => {
                if (reportType === 'all' || report.getAttribute('data-category') === reportType) {
                    report.style.display = 'block';
                } else {
                    report.style.display = 'none';
                }
            });

            // Load data for the selected report type
            try {
                const response = await fetch(`api/reports.php?action=get_report&type=${reportType}&period=${timePeriod}&from=${dateFrom}&to=${dateTo}`);
                const data = await response.json();

                if (data.success) {
                    // Update charts with new data
                    updateCharts(data.chart_data);
                }
            } catch (error) {
                console.error('Error loading reports:', error);
            }
        }

        // Toggle Advanced Filters
        function toggleAdvancedFilters() {
            const filters = document.getElementById('advancedFilters');
            if (filters.style.display === 'none') {
                filters.style.display = 'block';
            } else {
                filters.style.display = 'none';
            }
        }

        // View Detailed Report
        async function viewReport(reportType) {
            try {
                const response = await fetch(`api/reports.php?action=get_detailed_report&type=${reportType}`);
                const data = await response.json();

                if (data.success) {
                    document.getElementById('reportTitle').textContent = data.report.title;
                    document.getElementById('reportContent').innerHTML = data.report.html;
                    document.getElementById('detailedReportView').style.display = 'block';

                    // Scroll to report
                    document.getElementById('detailedReportView').scrollIntoView({ behavior: 'smooth' });
                }
            } catch (error) {
                console.error('Error viewing report:', error);
            }
        }

        // Close Report
        function closeReport() {
            document.getElementById('detailedReportView').style.display = 'none';
        }

        // Export Report
        async function exportReport(reportType, format) {
            try {
                const response = await fetch(`api/reports.php?action=export_report&type=${reportType}&format=${format}`);

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `report_${reportType}_${new Date().toISOString().split('T')[0]}.${format}`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                }
            } catch (error) {
                console.error('Error exporting report:', error);
                ajaxHandler.showError('حدث خطأ أثناء تصدير التقرير');
            }
        }

        // Export All Reports
        async function exportAllReports() {
            if (!confirm('هل تريد تصدير جميع التقارير؟ قد يستغرق ذلك بضع دقائق.')) return;

            try {
                const response = await fetch('api/reports.php?action=export_all_reports');

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `all_reports_${new Date().toISOString().split('T')[0]}.zip`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);

                    ajaxHandler.showSuccess('تم تصدير جميع التقارير بنجاح');
                }
            } catch (error) {
                console.error('Error exporting all reports:', error);
                ajaxHandler.showError('حدث خطأ أثناء تصدير التقارير');
            }
        }

        // Generate Custom Report
        function generateCustomReport() {
            $('#customReportModal').modal('show');
        }

        // Print Report
        function printReport() {
            const printContent = document.getElementById('reportContent').innerHTML;
            const originalContent = document.body.innerHTML;

            document.body.innerHTML = `
            <html>
                <head>
                    <title>${document.getElementById('reportTitle').textContent}</title>
                    <style>
                        body { font-family: Arial, sans-serif; direction: rtl; padding: 20px; }
                        .print-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                        .company-name { font-size: 24px; font-weight: bold; color: #333; }
                        .report-title { font-size: 20px; margin-top: 10px; }
                        .print-footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
                        th { background-color: #f2f2f2; }
                        @media print {
                            @page { margin: 0.5in; }
                        }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <div class="company-name">شركة تأجير السيارات المتقدمة</div>
                        <div class="report-title">${document.getElementById('reportTitle').textContent}</div>
                        <div>تاريخ الطباعة: ${new Date().toLocaleDateString('ar-EG')}</div>
                    </div>
                    ${printContent}
                    <div class="print-footer">
                        <p>تم إنشاء هذا التقرير تلقائياً بواسطة نظام إدارة شركة تأجير السيارات المتقدمة</p>
                        <p>جميع الحقوق محفوظة © ${new Date().getFullYear()}</p>
                    </div>
                </body>
            </html>
        `;

            window.print();
            document.body.innerHTML = originalContent;
            location.reload();
        }

        // Load Scheduled Reports
        async function loadScheduledReports() {
            try {
                const response = await fetch('api/reports.php?action=get_scheduled_reports');
                const data = await response.json();

                if (data.success) {
                    let html = '';

                    if (data.reports.length === 0) {
                        html = `
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="fas fa-clock fa-2x text-muted mb-3"></i>
                            <p class="text-muted mb-0">لا توجد تقارير مجدولة</p>
                        </td>
                    </tr>`;
                    } else {
                        data.reports.forEach(report => {
                            let statusBadge = '';
                            switch (report.status) {
                                case 'active':
                                    statusBadge = '<span class="badge bg-success">نشط</span>';
                                    break;
                                case 'paused':
                                    statusBadge = '<span class="badge bg-warning">معلق</span>';
                                    break;
                                case 'completed':
                                    statusBadge = '<span class="badge bg-info">مكتمل</span>';
                                    break;
                            }

                            html += `
                        <tr>
                            <td>${report.name}</td>
                            <td>${report.type}</td>
                            <td>${report.frequency}</td>
                            <td>${report.recipients}</td>
                            <td>${statusBadge}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-info me-1" onclick="editScheduledReport(${report.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteScheduledReport(${report.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                        });
                    }

                    document.getElementById('scheduledReportsBody').innerHTML = html;

                    // Initialize DataTable
                    $('#scheduledReportsTable').DataTable({
                        language: {
                            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading scheduled reports:', error);
            }
        }

        // Schedule New Report
        function scheduleNewReport() {
            // Implement scheduling functionality
            ajaxHandler.showInfo('سيتم إضافة هذه الميزة في الإصدار القادم');
        }

        // Custom Report Form Submit
        document.getElementById('customReportForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'create_custom_report');

            try {
                const response = await fetch('api/reports.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    ajaxHandler.showSuccess('تم إنشاء التقرير المخصص بنجاح');
                    $('#customReportModal').modal('hide');

                    // Download the report
                    if (data.download_url) {
                        setTimeout(() => {
                            window.open(data.download_url, '_blank');
                        }, 1000);
                    }
                } else {
                    document.getElementById('customReportResponse').innerHTML =
                        `<div class="alert alert-danger">${data.message}</div>`;
                }
            } catch (error) {
                console.error('Error creating custom report:', error);
                document.getElementById('customReportResponse').innerHTML =
                    '<div class="alert alert-danger">حدث خطأ أثناء إنشاء التقرير</div>';
            }
        });

        // Event Listeners
        document.getElementById('reportType').addEventListener('change', loadReports);
        document.getElementById('timePeriod').addEventListener('change', function () {
            const period = this.value;
            const today = new Date();

            if (period === 'today') {
                document.getElementById('dateFrom').value = today.toISOString().split('T')[0];
                document.getElementById('dateTo').value = today.toISOString().split('T')[0];
            } else if (period === 'yesterday') {
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                document.getElementById('dateFrom').value = yesterday.toISOString().split('T')[0];
                document.getElementById('dateTo').value = yesterday.toISOString().split('T')[0];
            } else if (period === 'week') {
                const firstDay = new Date(today.setDate(today.getDate() - today.getDay() + 1));
                document.getElementById('dateFrom').value = firstDay.toISOString().split('T')[0];
                document.getElementById('dateTo').value = new Date().toISOString().split('T')[0];
            } else if (period === 'month') {
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                document.getElementById('dateFrom').value = firstDay.toISOString().split('T')[0];
                document.getElementById('dateTo').value = new Date().toISOString().split('T')[0];
            } else if (period === 'quarter') {
                const quarter = Math.floor((today.getMonth() + 3) / 3);
                const firstMonth = (quarter - 1) * 3;
                const firstDay = new Date(today.getFullYear(), firstMonth, 1);
                document.getElementById('dateFrom').value = firstDay.toISOString().split('T')[0];
                document.getElementById('dateTo').value = new Date().toISOString().split('T')[0];
            } else if (period === 'year') {
                const firstDay = new Date(today.getFullYear(), 0, 1);
                document.getElementById('dateFrom').value = firstDay.toISOString().split('T')[0];
                document.getElementById('dateTo').value = new Date().toISOString().split('T')[0];
            }
        });
    </script>
</body>

</html>