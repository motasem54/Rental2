<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';
require_once '../../core/WhatsAppAPI.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

// تحويل الـTitle
$page_title = "إدارة عمليات التأجير - نظام إدارة شركة تأجير السيارات";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>

    <link rel="stylesheet" href="../../assets/css/glassmorphism.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        .rental-card {
            border-left: 5px solid;
            transition: all 0.3s ease;
        }

        .rental-status-pending {
            border-left-color: #ffc107;
        }

        .rental-status-active {
            border-left-color: #28a745;
        }

        .rental-status-completed {
            border-left-color: #17a2b8;
        }

        .rental-status-cancelled {
            border-left-color: #dc3545;
        }

        .company-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            color: white;
        }

        .company-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .company-logo i {
            font-size: 2.5rem;
            color: var(--primary);
        }

        .rental-timeline {
            position: relative;
            padding-right: 30px;
        }

        .rental-timeline::before {
            content: '';
            position: absolute;
            right: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--primary), transparent);
        }

        .rental-timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .rental-timeline-item::before {
            content: '';
            position: absolute;
            right: -20px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary);
            border: 2px solid white;
            box-shadow: 0 0 0 3px rgba(255, 87, 34, 0.2);
        }
    </style>
</head>

<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Company Header -->
            <div class="company-header">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <div class="company-logo">
                            <i class="fas fa-car"></i>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h2 class="mb-1">شركة تأجير السيارات المتقدمة</h2>
                        <p class="mb-0 opacity-75">نظام إدارة عمليات تأجير السيارات الاحترافي</p>
                    </div>
                    <div class="col-md-2 text-end">
                        <small>الإصدار: 2.0.0</small>
                    </div>
                </div>
            </div>

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-file-contract me-2"></i> إدارة عمليات التأجير</h2>
                    <small class="text-muted">إدارة وتتبع جميع عمليات تأجير سيارات الشركة</small>
                </div>

                <div>
                    <button class="btn btn-orange me-2" onclick="window.location.href='create.php'">
                        <i class="fas fa-plus me-2"></i> عملية تأجير جديدة
                    </button>

                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-light dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-filter me-2"></i> تصفية
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="filterRentals('all')">جميع العمليات</a></li>
                            <li><a class="dropdown-item" href="#" onclick="filterRentals('pending')">عمليات قيد
                                    الانتظار</a></li>
                            <li><a class="dropdown-item" href="#" onclick="filterRentals('active')">عمليات تأجير
                                    نشطة</a></li>
                            <li><a class="dropdown-item" href="#" onclick="filterRentals('today')">عمليات تأجير
                                    اليوم</a></li>
                            <li><a class="dropdown-item" href="#" onclick="filterRentals('upcoming')">عمليات تأجير
                                    قادمة</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="glass-card stat-card">
                        <div class="card-icon bg-warning-gradient mx-auto">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="number" id="pendingRentals">0</div>
                        <div class="label">عمليات قيد الانتظار</div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="glass-card stat-card">
                        <div class="card-icon bg-success-gradient mx-auto">
                            <i class="fas fa-play-circle"></i>
                        </div>
                        <div class="number" id="activeRentals">0</div>
                        <div class="label">عمليات تأجير نشطة</div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="glass-card stat-card">
                        <div class="card-icon bg-info-gradient mx-auto">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="number" id="completedRentals">0</div>
                        <div class="label">عمليات مكتملة</div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="glass-card stat-card">
                        <div class="card-icon bg-danger-gradient mx-auto">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="number" id="cancelledRentals">0</div>
                        <div class="label">عمليات ملغية</div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="glass-card p-3 mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control glass-input" id="searchRentals"
                            placeholder="بحث برقم العملية أو العميل...">
                    </div>

                    <div class="col-md-2">
                        <select class="form-select glass-input" id="statusFilter">
                            <option value="">جميع الحالات</option>
                            <option value="pending">قيد الانتظار</option>
                            <option value="active">تأجير نشط</option>
                            <option value="completed">مكتملة</option>
                            <option value="cancelled">ملغية</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="text" class="form-control glass-input" id="dateFrom" placeholder="من تاريخ"
                            data-toggle="flatpickr">
                    </div>

                    <div class="col-md-2">
                        <input type="text" class="form-control glass-input" id="dateTo" placeholder="إلى تاريخ"
                            data-toggle="flatpickr">
                    </div>

                    <div class="col-md-2">
                        <select class="form-select glass-input" id="carFilter">
                            <option value="">جميع السيارات</option>
                            <!-- Cars will be loaded via AJAX -->
                        </select>
                    </div>

                    <div class="col-md-1">
                        <button class="btn btn-outline-light w-100" onclick="resetRentalFilters()">
                            <i class="fas fa-redo"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Rentals Table -->
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">قائمة عمليات التأجير</h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="autoRefreshRentals" checked>
                        <label class="form-check-label" for="autoRefreshRentals">تحديث تلقائي</label>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark table-hover" id="rentalsTable">
                        <thead>
                            <tr>
                                <th>رقم العملية</th>
                                <th>العميل</th>
                                <th>السيارة</th>
                                <th>فترة التأجير</th>
                                <th>المدة</th>
                                <th>قيمة التأجير</th>
                                <th>حالة العملية</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="rentalsTableBody">
                            <!-- Rentals will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation" class="mt-3">
                    <ul class="pagination justify-content-center" id="rentalsPagination">
                        <!-- Pagination will be loaded via AJAX -->
                    </ul>
                </nav>
            </div>

            <!-- Calendar View -->
            <div class="glass-card mt-4">
                <h5 class="mb-3"><i class="fas fa-calendar-alt me-2"></i> تقويم عمليات التأجير</h5>
                <div id="rentalsCalendar" style="min-height: 400px;"></div>
            </div>

            <!-- Rental Process Timeline -->
            <div class="glass-card mt-4">
                <h5 class="mb-3"><i class="fas fa-project-diagram me-2"></i> مراحل عملية التأجير</h5>
                <div class="rental-timeline">
                    <div class="rental-timeline-item">
                        <h6><i class="fas fa-search text-info me-2"></i> 1. البحث والاختيار</h6>
                        <p class="text-muted mb-0">يقوم العميل باختيار السيارة المناسبة من معرض الشركة</p>
                    </div>

                    <div class="rental-timeline-item">
                        <h6><i class="fas fa-file-contract text-warning me-2"></i> 2. تعبئة البيانات</h6>
                        <p class="text-muted mb-0">تعبئة نموذج طلب التأجير والبيانات الشخصية</p>
                    </div>

                    <div class="rental-timeline-item">
                        <h6><i class="fas fa-money-check-alt text-success me-2"></i> 3. الدفع والتأمين</h6>
                        <p class="text-muted mb-0">دفع الدفعة المقدمة وتوقيع عقد التأمين</p>
                    </div>

                    <div class="rental-timeline-item">
                        <h6><i class="fas fa-car text-primary me-2"></i> 4. تسليم السيارة</h6>
                        <p class="text-muted mb-0">تسليم السيارة للعميل مع فحص الحالة</p>
                    </div>

                    <div class="rental-timeline-item">
                        <h6><i class="fas fa-undo text-secondary me-2"></i> 5. إرجاع السيارة</h6>
                        <p class="text-muted mb-0">فحص السيارة عند الإرجاع وتحصيل المبالغ المستحقة</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rental Details Modal -->
    <div class="modal fade" id="rentalDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content glass-card">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i> تفاصيل عملية التأجير</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="rentalDetailsContainer">
                    <!-- Details will be loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="button" class="btn btn-primary" onclick="printRentalInvoice()">
                        <i class="fas fa-print me-2"></i> طباعة عقد التأجير
                    </button>
                    <button type="button" class="btn btn-success" onclick="sendRentalReminder()">
                        <i class="fab fa-whatsapp me-2"></i> إرسال تذكير
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content glass-card">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-sync-alt me-2"></i> تحديث حالة العملية</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="updateStatusForm">
                        <input type="hidden" id="rentalId" name="rental_id">

                        <div class="mb-3">
                            <label class="form-label">الحالة الجديدة</label>
                            <select class="form-select glass-input" id="newStatus" name="status" required>
                                <option value="pending">قيد الانتظار</option>
                                <option value="active">تأجير نشط</option>
                                <option value="completed">مكتملة</option>
                                <option value="cancelled">ملغية</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea class="form-control glass-input" id="statusNotes" name="notes"
                                rows="3"></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-orange">
                                <i class="fas fa-save me-2"></i> حفظ التغييرات
                            </button>
                        </div>
                    </form>

                    <div id="statusUpdateResponse" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="../../assets/js/app.js"></script>
    <script src="../../assets/js/ajax-handler.js"></script>

    <script>
        // Initialize
        document.addEventListener('DOMContentLoaded', function () {
            loadRentals();
            loadRentalStats();
            loadCarsForFilter();
            initCalendar();
            initFlatpickr();

            // Auto-refresh
            setInterval(() => {
                if (document.getElementById('autoRefreshRentals').checked) {
                    loadRentals();
                    loadRentalStats();
                }
            }, 30000);
        });

        // Initialize Flatpickr
        function initFlatpickr() {
            flatpickr("#dateFrom, #dateTo", {
                dateFormat: "Y-m-d",
                locale: "ar",
                disableMobile: true
            });
        }

        // Load Rentals
        async function loadRentals(page = 1) {
            const search = document.getElementById('searchRentals').value;
            const status = document.getElementById('statusFilter').value;
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            const carId = document.getElementById('carFilter').value;

            try {
                const response = await fetch(`../../api/rentals.php?action=get_rentals&page=${page}&search=${search}&status=${status}&date_from=${dateFrom}&date_to=${dateTo}&car_id=${carId}`);
                const data = await response.json();

                if (data.success) {
                    updateRentalsTable(data.rentals);
                    updateRentalsPagination(data.pagination);
                    updateCalendar(data.rentals);
                }
            } catch (error) {
                console.error('Error loading rentals:', error);
            }
        }

        // Load Rental Statistics
        async function loadRentalStats() {
            try {
                const response = await fetch('../../api/rentals.php?action=get_stats');
                const data = await response.json();

                if (data.success) {
                    document.getElementById('pendingRentals').textContent = data.stats.pending;
                    document.getElementById('activeRentals').textContent = data.stats.active;
                    document.getElementById('completedRentals').textContent = data.stats.completed;
                    document.getElementById('cancelledRentals').textContent = data.stats.cancelled;
                }
            } catch (error) {
                console.error('Error loading rental stats:', error);
            }
        }

        // Load Cars for Filter
        async function loadCarsForFilter() {
            try {
                const response = await fetch('../../api/cars.php?action=get_cars&limit=100');
                const data = await response.json();

                if (data.success) {
                    const select = document.getElementById('carFilter');
                    select.innerHTML = '<option value="">جميع السيارات</option>';

                    data.cars.forEach(car => {
                        const option = document.createElement('option');
                        option.value = car.id;
                        option.textContent = `${car.brand} ${car.model} - ${car.plate_number}`;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading cars:', error);
            }
        }

        // Update Rentals Table
        function updateRentalsTable(rentals) {
            let html = '';

            if (rentals.length === 0) {
                html = `
            <tr>
                <td colspan="8" class="text-center py-5">
                    <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد عمليات تأجير</h5>
                    <p class="text-muted mb-0">قم بإنشاء عملية تأجير جديدة لتبدأ</p>
                </td>
            </tr>`;
            } else {
                rentals.forEach(rental => {
                    const statusClass = getRentalStatusClass(rental.status);
                    const statusText = getRentalStatusText(rental.status);

                    html += `
                <tr class="rental-row" data-rental-id="${rental.id}">
                    <td>
                        <strong>#${rental.rental_number}</strong><br>
                        <small class="text-muted">${rental.created_at}</small>
                    </td>
                    <td>
                        <strong>${rental.customer_name}</strong><br>
                        <small class="text-muted">${rental.customer_phone}</small>
                    </td>
                    <td>
                        ${rental.car_brand} ${rental.car_model}<br>
                        <small class="text-muted">${rental.car_plate}</small>
                    </td>
                    <td>
                        ${rental.start_date} إلى ${rental.end_date}<br>
                        <small class="text-muted">${rental.days} يوم</small>
                    </td>
                    <td>${rental.days} يوم</td>
                    <td>
                        <strong class="text-orange">${rental.total} ₪</strong><br>
                        <small class="text-muted">${rental.paid ? 'مدفوع' : 'غير مدفوع'}</small>
                    </td>
                    <td>
                        <span class="badge ${statusClass}">${statusText}</span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-info me-1" onclick="viewRentalDetails(${rental.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning me-1" onclick="updateRentalStatus(${rental.id}, '${rental.status}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="sendRentalWhatsApp(${rental.id})">
                            <i class="fab fa-whatsapp"></i>
                        </button>
                    </td>
                </tr>`;
                });
            }

            document.getElementById('rentalsTableBody').innerHTML = html;
        }

        // Get Rental Status Class
        function getRentalStatusClass(status) {
            const classMap = {
                'pending': 'bg-warning',
                'active': 'bg-success',
                'completed': 'bg-info',
                'cancelled': 'bg-danger'
            };
            return classMap[status] || 'bg-secondary';
        }

        // Get Rental Status Text
        function getRentalStatusText(status) {
            const textMap = {
                'pending': 'قيد الانتظار',
                'active': 'تأجير نشط',
                'completed': 'مكتملة',
                'cancelled': 'ملغية'
            };
            return textMap[status] || status;
        }

        // Filter Rentals
        function filterRentals(filter) {
            switch (filter) {
                case 'pending':
                    document.getElementById('statusFilter').value = 'pending';
                    break;
                case 'active':
                    document.getElementById('statusFilter').value = 'active';
                    break;
                case 'today':
                    const today = new Date().toISOString().split('T')[0];
                    document.getElementById('dateFrom').value = today;
                    document.getElementById('dateTo').value = today;
                    break;
                case 'upcoming':
                    const tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    document.getElementById('dateFrom').value = tomorrow.toISOString().split('T')[0];
                    document.getElementById('dateTo').value = '';
                    break;
            }

            loadRentals();
        }

        // Reset Filters
        function resetRentalFilters() {
            document.getElementById('searchRentals').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('dateFrom').value = '';
            document.getElementById('dateTo').value = '';
            document.getElementById('carFilter').value = '';
            loadRentals();
        }

        // View Rental Details
        async function viewRentalDetails(rentalId) {
            try {
                const response = await fetch(`../../api/rentals.php?action=get_rental_details&id=${rentalId}`);
                const data = await response.json();

                if (data.success) {
                    const rental = data.rental;
                    const details = `
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="glass-card p-3">
                            <h4 class="mb-3 text-center">
                                <i class="fas fa-file-contract me-2"></i>
                                عقد تأجير سيارة - رقم العملية: #${rental.rental_number}
                            </h4>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="glass-card p-3 mb-3">
                            <h5><i class="fas fa-user me-2"></i> معلومات المستأجر</h5>
                            <table class="table table-sm table-dark">
                                <tr>
                                    <td>اسم المستأجر:</td>
                                    <td><strong>${rental.customer_name}</strong></td>
                                </tr>
                                <tr>
                                    <td>رقم الهاتف:</td>
                                    <td>${rental.customer_phone}</td>
                                </tr>
                                <tr>
                                    <td>البريد الإلكتروني:</td>
                                    <td>${rental.customer_email}</td>
                                </tr>
                                <tr>
                                    <td>رقم الرخصة:</td>
                                    <td>${rental.customer_license}</td>
                                </tr>
                                <tr>
                                    <td>نوع العميل:</td>
                                    <td>${rental.customer_type || 'فردي'}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="glass-card p-3">
                            <h5><i class="fas fa-car me-2"></i> معلومات المركبة المؤجرة</h5>
                            <table class="table table-sm table-dark">
                                <tr>
                                    <td>نوع المركبة:</td>
                                    <td><strong>${rental.car_brand} ${rental.car_model} ${rental.car_year}</strong></td>
                                </tr>
                                <tr>
                                    <td>رقم اللوحة:</td>
                                    <td>${rental.car_plate}</td>
                                </tr>
                                <tr>
                                    <td>اللون:</td>
                                    <td>${rental.car_color}</td>
                                </tr>
                                <tr>
                                    <td>رقم الشاسيه:</td>
                                    <td>${rental.car_chassis || 'غير محدد'}</td>
                                </tr>
                                <tr>
                                    <td>رقم المحرك:</td>
                                    <td>${rental.car_engine || 'غير محدد'}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="glass-card p-3 mb-3">
                            <h5><i class="fas fa-calendar-alt me-2"></i> تفاصيل عملية التأجير</h5>
                            <table class="table table-sm table-dark">
                                <tr>
                                    <td>تاريخ العملية:</td>
                                    <td>${rental.created_at}</td>
                                </tr>
                                <tr>
                                    <td>فترة التأجير:</td>
                                    <td>من ${rental.start_date} إلى ${rental.end_date}</td>
                                </tr>
                                <tr>
                                    <td>مدة التأجير:</td>
                                    <td>${rental.days} يوم</td>
                                </tr>
                                <tr>
                                    <td>موقع الاستلام:</td>
                                    <td>${rental.pickup_location || 'فرع الشركة الرئيسي'}</td>
                                </tr>
                                <tr>
                                    <td>موقع الإرجاع:</td>
                                    <td>${rental.return_location || 'فرع الشركة الرئيسي'}</td>
                                </tr>
                                <tr>
                                    <td>حالة العملية:</td>
                                    <td><span class="badge ${getRentalStatusClass(rental.status)}">${getRentalStatusText(rental.status)}</span></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="glass-card p-3">
                            <h5><i class="fas fa-money-bill-wave me-2"></i> التفاصيل المالية</h5>
                            <div class="cost-breakdown">
                                <div class="cost-item">
                                    <span>السعر اليومي:</span>
                                    <span>${rental.daily_rate} ₪ × ${rental.days} يوم</span>
                                </div>
                                <div class="cost-item">
                                    <span>تكلفة الصيانة:</span>
                                    <span>${rental.maintenance_cost} ₪</span>
                                </div>
                                <div class="cost-item">
                                    <span>تكلفة التأمين:</span>
                                    <span>${rental.insurance_cost} ₪</span>
                                </div>
                                <div class="cost-item">
                                    <span>الضريبة (${rental.tax_rate}%):</span>
                                    <span>${rental.tax} ₪</span>
                                </div>
                                <div class="cost-item total">
                                    <span>الإجمالي:</span>
                                    <span>${rental.total} ₪</span>
                                </div>
                                <div class="cost-item">
                                    <span>الدفعة المقدمة:</span>
                                    <span>${rental.deposit} ₪</span>
                                </div>
                                <div class="cost-item">
                                    <span>المتبقي:</span>
                                    <span>${rental.remaining} ₪</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="glass-card p-3">
                            <h5><i class="fas fa-sticky-note me-2"></i> شروط وأحكام التأجير</h5>
                            <div class="terms-and-conditions">
                                <ol class="mb-0">
                                    <li>يجب على المستأجر العناية بالمركبة أثناء فترة التأجير.</li>
                                    <li>يتم احتساب رسوم إضافية في حالة التأخير في إرجاع المركبة.</li>
                                    <li>المسؤولية الكاملة عن المخالفات المرورية تقع على عاتق المستأجر.</li>
                                    <li>يجب إرجاع المركبة بنفس حالة الاستلام مع صندوق وقود كامل.</li>
                                    <li>تلتزم الشركة بتوفير مركبة بديلة في حال تعطل المركبة المؤجرة.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>`;

                    document.getElementById('rentalDetailsContainer').innerHTML = details;
                    window.currentRentalId = rentalId;
                    $('#rentalDetailsModal').modal('show');
                }
            } catch (error) {
                console.error('Error loading rental details:', error);
            }
        }

        // Update Rental Status
        function updateRentalStatus(rentalId, currentStatus) {
            document.getElementById('rentalId').value = rentalId;
            document.getElementById('newStatus').value = currentStatus;
            $('#updateStatusModal').modal('show');
        }

        // Send WhatsApp Reminder
        async function sendRentalWhatsApp(rentalId) {
            if (!confirm('هل تريد إرسال تذكير عبر واتساب للمستأجر؟')) return;

            try {
                const response = await fetch('../../api/whatsapp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=send_rental_reminder&rental_id=${rentalId}`
                });

                const data = await response.json();

                if (data.success) {
                    showToast('success', 'تم إرسال التذكير بنجاح');
                } else {
                    showToast('error', data.message);
                }
            } catch (error) {
                console.error('Error sending WhatsApp:', error);
                showToast('error', 'حدث خطأ أثناء إرسال التذكير');
            }
        }

        // Print Rental Invoice
        function printRentalInvoice() {
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
            <html>
                <head>
                    <title>عقد تأجير سيارة - العملية #${window.currentRentalId}</title>
                    <style>
                        body { font-family: Arial, sans-serif; direction: rtl; padding: 20px; }
                        .contract-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                        .company-name { font-size: 24px; font-weight: bold; color: #333; }
                        .contract-title { font-size: 20px; margin-top: 10px; }
                        .contract-details { margin-bottom: 20px; }
                        .contract-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                        .contract-table th, .contract-table td { border: 1px solid #ddd; padding: 8px; text-align: right; }
                        .contract-table th { background-color: #f2f2f2; }
                        .total-row { font-weight: bold; background-color: #f8f9fa; }
                        .terms-section { margin-top: 30px; border-top: 1px solid #333; padding-top: 20px; }
                        .signature-section { margin-top: 50px; }
                        .signature-line { border-top: 1px solid #333; width: 200px; display: inline-block; margin: 0 20px; }
                        @media print {
                            .no-print { display: none; }
                            body { font-size: 12px; }
                        }
                    </style>
                </head>
                <body>
                    <div class="contract-header">
                        <div class="company-name">شركة تأجير السيارات المتقدمة</div>
                        <div class="contract-title">عقد تأجير سيارة</div>
                        <div>رقم العقد: #${window.currentRentalId}</div>
                        <div>تاريخ الإصدار: ${new Date().toLocaleDateString('ar-EG')}</div>
                    </div>
                    ${document.getElementById('rentalDetailsContainer').innerHTML}
                    
                    <div class="signature-section">
                        <div style="text-align: center;">
                            <div style="display: inline-block; margin: 0 40px;">
                                <div>توقيع المستأجر</div>
                                <div class="signature-line"></div>
                                <div>الاسم: ____________________</div>
                                <div>التاريخ: ____________________</div>
                            </div>
                            
                            <div style="display: inline-block; margin: 0 40px;">
                                <div>توقيع الممثل القانوني للشركة</div>
                                <div class="signature-line"></div>
                                <div>الاسم: ____________________</div>
                                <div>التاريخ: ____________________</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="no-print" style="text-align: center; margin-top: 30px;">
                        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                            طباعة العقد
                        </button>
                    </div>
                </body>
            </html>
        `);
            printWindow.document.close();
        }

        // Event Listeners
        document.getElementById('searchRentals').addEventListener('input', debounce(() => loadRentals(), 500));
        document.getElementById('statusFilter').addEventListener('change', () => loadRentals());
        document.getElementById('dateFrom').addEventListener('change', () => loadRentals());
        document.getElementById('dateTo').addEventListener('change', () => loadRentals());
        document.getElementById('carFilter').addEventListener('change', () => loadRentals());

        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Show Toast Notification
        function showToast(type, message) {
            const toast = document.createElement('div');
            toast.className = `toast position-fixed bottom-0 end-0 m-3 bg-${type === 'success' ? 'success' : 'danger'} text-white p-3 rounded`;
            toast.style.zIndex = '9999';
            toast.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                <span>${message}</span>
            </div>
        `;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    </script>
</body>

</html>