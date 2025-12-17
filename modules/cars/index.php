<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';
require_once '../../core/AjaxHandler.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$handler = new AjaxHandler();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة السيارات</title>

    <link rel="stylesheet" href="../../assets/css/glassmorphism.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css">

    <style>
        .car-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .car-image {
            height: 200px;
            overflow: hidden;
            border-radius: 10px 10px 0 0;
        }

        .car-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .car-card:hover .car-image img {
            transform: scale(1.05);
        }

        .status-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .available {
            background: #28a745;
        }

        .rented {
            background: #ffc107;
            color: #000;
        }

        .maintenance {
            background: #dc3545;
        }

        .reserved {
            background: #17a2b8;
        }

        .action-buttons {
            position: absolute;
            top: 10px;
            right: 10px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .car-card:hover .action-buttons {
            opacity: 1;
        }

        .btn-action {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 5px;
            border: none;
        }

        .filter-bar {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-box {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }

        .stat-box .number {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-box .label {
            font-size: 0.9rem;
            opacity: 0.8;
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
                    <h2><i class="fas fa-car me-2"></i> إدارة السيارات</h2>
                    <small class="text-muted">إدارة وتتبع جميع سيارات الشركة</small>
                </div>

                <div>
                    <button class="btn btn-orange me-2" data-bs-toggle="modal" data-bs-target="#addCarModal">
                        <i class="fas fa-plus me-2"></i> إضافة سيارة
                    </button>

                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-light dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-download me-2"></i> تصدير
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exportData('excel')"><i
                                        class="fas fa-file-excel me-2"></i> Excel</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportData('pdf')"><i
                                        class="fas fa-file-pdf me-2"></i> PDF</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportData('print')"><i
                                        class="fas fa-print me-2"></i> طباعة</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="stats-overview">
                <div class="stat-box">
                    <div class="number text-success" id="totalCars">0</div>
                    <div class="label">إجمالي السيارات</div>
                </div>

                <div class="stat-box">
                    <div class="number text-info" id="availableCars">0</div>
                    <div class="label">متاحة للإيجار</div>
                </div>

                <div class="stat-box">
                    <div class="number text-warning" id="rentedCars">0</div>
                    <div class="label">مؤجرة حالياً</div>
                </div>

                <div class="stat-box">
                    <div class="number text-danger" id="maintenanceCars">0</div>
                    <div class="label">تحت الصيانة</div>
                </div>

                <div class="stat-box">
                    <div class="number" id="totalValue">0 ₪</div>
                    <div class="label">القيمة الإجمالية</div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control glass-input" id="searchInput"
                            placeholder="بحث بالماركة أو الموديل أو الرقم...">
                    </div>

                    <div class="col-md-2">
                        <select class="form-select glass-input" id="statusFilter">
                            <option value="">جميع الحالات</option>
                            <option value="available">متاحة</option>
                            <option value="rented">مؤجرة</option>
                            <option value="maintenance">صيانة</option>
                            <option value="reserved">محجوزة</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select class="form-select glass-input" id="brandFilter">
                            <option value="">جميع الماركات</option>
                            <!-- Brands will be loaded via AJAX -->
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="number" class="form-control glass-input" id="yearFrom" placeholder="من سنة"
                            min="2000" max="2024">
                    </div>

                    <div class="col-md-2">
                        <input type="number" class="form-control glass-input" id="yearTo" placeholder="إلى سنة"
                            min="2000" max="2024">
                    </div>

                    <div class="col-md-1">
                        <button class="btn btn-outline-light w-100" onclick="resetFilters()">
                            <i class="fas fa-redo"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cars Grid/Table Toggle -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="viewMode" id="gridView" autocomplete="off" checked>
                    <label class="btn btn-outline-light" for="gridView">
                        <i class="fas fa-th-large"></i>
                    </label>

                    <input type="radio" class="btn-check" name="viewMode" id="tableView" autocomplete="off">
                    <label class="btn btn-outline-light" for="tableView">
                        <i class="fas fa-list"></i>
                    </label>
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="autoRefresh" checked>
                    <label class="form-check-label" for="autoRefresh">تحديث تلقائي</label>
                </div>
            </div>

            <!-- Cars Grid View -->
            <div class="row" id="carsGridView">
                <!-- Cars will be loaded via AJAX -->
            </div>

            <!-- Cars Table View -->
            <div class="table-responsive d-none" id="carsTableView">
                <table class="table table-dark table-hover" id="carsTable">
                    <thead>
                        <tr>
                            <th>الصورة</th>
                            <th>السيارة</th>
                            <th>الرقم</th>
                            <th>السعر/يوم</th>
                            <th>التأمين</th>
                            <th>الصيانة/يوم</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Table data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center" id="pagination">
                    <!-- Pagination will be loaded via AJAX -->
                </ul>
            </nav>
        </div>
    </div>

    <!-- Add Car Modal -->
    <div class="modal fade" id="addCarModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content glass-card">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i> إضافة سيارة جديدة</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addCarForm" class="ajax-form" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">الماركة <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control glass-input" name="brand" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">الموديل <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control glass-input" name="model" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">سنة الصنع <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control glass-input" name="year" min="2000"
                                        max="2024" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">رقم اللوحة <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control glass-input" name="plate_number" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">اللون</label>
                                    <input type="text" class="form-control glass-input" name="color">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">السعر اليومي (₪) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control glass-input" name="daily_rate"
                                        required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">تكلفة الصيانة/يوم (₪)</label>
                                    <input type="number" step="0.01" class="form-control glass-input"
                                        name="maintenance_cost_per_day" value="15">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">نوع التأمين</label>
                                    <select class="form-select glass-input" name="insurance_type">
                                        <option value="third_party">ضد الغير</option>
                                        <option value="partial">تأمين جزئي</option>
                                        <option value="full">تأمين كامل</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">صورة السيارة</label>
                                    <input type="file" class="form-control glass-input" name="image" accept="image/*">
                                    <small class="text-muted">الحد الأقصى 5MB</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">المميزات</label>
                                    <textarea class="form-control glass-input" name="features" rows="3"
                                        placeholder="مكيف, بلوتوث, كاميرا خلفية..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-orange">
                                <i class="fas fa-save me-2"></i> حفظ السيارة
                            </button>
                        </div>
                    </form>

                    <div id="addCarResponse" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Car Modal -->
    <div class="modal fade" id="editCarModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content glass-card">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> تعديل السيارة</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="editCarFormContainer">
                    <!-- Form will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Car Details Modal -->
    <div class="modal fade" id="carDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content glass-card">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i> تفاصيل السيارة</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="carDetailsContainer">
                    <!-- Details will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
    <script src="../../assets/js/app.js"></script>
    <script src="../../assets/js/ajax-handler.js"></script>

    <script>
        // Initialize
        document.addEventListener('DOMContentLoaded', function () {
            loadCars();
            loadCarStats();
            loadBrands();
            initEventListeners();

            // Auto-refresh every 60 seconds if enabled
            setInterval(() => {
                if (document.getElementById('autoRefresh').checked) {
                    loadCars();
                    loadCarStats();
                }
            }, 60000);
        });

        // Load Cars
        async function loadCars(page = 1) {
            const search = document.getElementById('searchInput').value;
            const status = document.getElementById('statusFilter').value;
            const brand = document.getElementById('brandFilter').value;
            const yearFrom = document.getElementById('yearFrom').value;
            const yearTo = document.getElementById('yearTo').value;

            try {
                const response = await fetch(`../../api/cars.php?action=get_cars&page=${page}&search=${search}&status=${status}&brand=${brand}&year_from=${yearFrom}&year_to=${yearTo}`);
                const data = await response.json();

                if (data.success) {
                    updateCarsView(data.cars);
                    updatePagination(data.pagination);
                }
            } catch (error) {
                console.error('Error loading cars:', error);
            }
        }

        // Load Car Statistics
        async function loadCarStats() {
            try {
                const response = await fetch('../../api/cars.php?action=get_stats');
                const data = await response.json();

                if (data.success) {
                    document.getElementById('totalCars').textContent = data.stats.total;
                    document.getElementById('availableCars').textContent = data.stats.available;
                    document.getElementById('rentedCars').textContent = data.stats.rented;
                    document.getElementById('maintenanceCars').textContent = data.stats.maintenance;
                    document.getElementById('totalValue').textContent = data.stats.total_value + ' ₪';
                }
            } catch (error) {
                console.error('Error loading car stats:', error);
            }
        }

        // Load Brands for Filter
        async function loadBrands() {
            try {
                const response = await fetch('../../api/cars.php?action=get_brands');
                const data = await response.json();

                if (data.success) {
                    const select = document.getElementById('brandFilter');
                    data.brands.forEach(brand => {
                        const option = document.createElement('option');
                        option.value = brand;
                        option.textContent = brand;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading brands:', error);
            }
        }

        // Update Cars View
        function updateCarsView(cars) {
            const isGridView = document.getElementById('gridView').checked;

            if (isGridView) {
                updateGridView(cars);
            } else {
                updateTableView(cars);
            }
        }

        // Update Grid View
        function updateGridView(cars) {
            let html = '';

            if (cars.length === 0) {
                html = `
            <div class="col-12 text-center py-5">
                <i class="fas fa-car fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">لا توجد سيارات</h4>
                <p class="text-muted">أضف سيارة جديدة لتبدأ</p>
            </div>`;
            } else {
                cars.forEach(car => {
                    html += `
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="car-card glass-card position-relative">
                        <div class="car-image position-relative">
                            <img src="${car.image || '../../assets/images/default-car.jpg'}" 
                                 alt="${car.brand} ${car.model}">
                            <span class="status-badge ${car.status}">${getStatusText(car.status)}</span>
                            
                            <div class="action-buttons">
                                <button class="btn-action btn-info" onclick="editCar(${car.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-action btn-danger" onclick="deleteCar(${car.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="btn-action btn-warning" onclick="viewCarDetails(${car.id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="p-3">
                            <h5 class="mb-2">${car.brand} ${car.model}</h5>
                            <p class="text-muted mb-2">${car.year} • ${car.color || 'غير محدد'}</p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block">رقم اللوحة</small>
                                    <strong>${car.plate_number}</strong>
                                </div>
                                
                                <div class="text-end">
                                    <small class="text-muted d-block">السعر اليومي</small>
                                    <strong class="text-orange">${car.daily_rate} ₪</strong>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button class="btn btn-sm btn-outline-light w-100" 
                                        onclick="bookCar(${car.id})" ${car.status !== 'available' ? 'disabled' : ''}>
                                    <i class="fas fa-calendar-check me-2"></i> حجز الآن
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
                });
            }

            document.getElementById('carsGridView').innerHTML = html;
            document.getElementById('carsGridView').classList.remove('d-none');
            document.getElementById('carsTableView').classList.add('d-none');
        }

        // Update Table View
        function updateTableView(cars) {
            let html = '';

            if (cars.length === 0) {
                html = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <i class="fas fa-car fa-2x text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد سيارات</h5>
                </td>
            </tr>`;
            } else {
                cars.forEach(car => {
                    html += `
                <tr>
                    <td>
                        <img src="${car.image || '../../assets/images/default-car.jpg'}" 
                             alt="${car.brand} ${car.model}" 
                             style="width: 60px; height: 40px; object-fit: cover; border-radius: 5px;">
                    </td>
                    <td>
                        <strong>${car.brand} ${car.model}</strong><br>
                        <small class="text-muted">${car.year}</small>
                    </td>
                    <td>${car.plate_number}</td>
                    <td>${car.daily_rate} ₪</td>
                    <td>${car.insurance_type === 'full' ? 'كامل' : car.insurance_type === 'partial' ? 'جزئي' : 'ضد الغير'}</td>
                    <td>${car.maintenance_cost_per_day} ₪</td>
                    <td>
                        <span class="badge ${getStatusClass(car.status)}">
                            ${getStatusText(car.status)}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-info me-1" onclick="editCar(${car.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger me-1" onclick="deleteCar(${car.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="viewCarDetails(${car.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>`;
                });
            }

            document.getElementById('carsTable').querySelector('tbody').innerHTML = html;
            document.getElementById('carsGridView').classList.add('d-none');
            document.getElementById('carsTableView').classList.remove('d-none');
        }

        // Update Pagination
        function updatePagination(pagination) {
            let html = '';

            if (pagination.pages > 1) {
                // Previous button
                html += `
            <li class="page-item ${pagination.current_page <= 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadCars(${pagination.current_page - 1})">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>`;

                // Page numbers
                for (let i = 1; i <= pagination.pages; i++) {
                    if (i === pagination.current_page) {
                        html += `<li class="page-item active"><a class="page-link" href="#">${i}</a></li>`;
                    } else if (i >= pagination.current_page - 2 && i <= pagination.current_page + 2) {
                        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadCars(${i})">${i}</a></li>`;
                    }
                }

                // Next button
                html += `
            <li class="page-item ${pagination.current_page >= pagination.pages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadCars(${pagination.current_page + 1})">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>`;
            }

            document.getElementById('pagination').innerHTML = html;
        }

        // Get Status Text
        function getStatusText(status) {
            const statusMap = {
                'available': 'متاحة',
                'rented': 'مؤجرة',
                'maintenance': 'صيانة',
                'reserved': 'محجوزة'
            };
            return statusMap[status] || status;
        }

        // Get Status Class
        function getStatusClass(status) {
            const classMap = {
                'available': 'bg-success',
                'rented': 'bg-warning',
                'maintenance': 'bg-danger',
                'reserved': 'bg-info'
            };
            return classMap[status] || 'bg-secondary';
        }

        // Initialize Event Listeners
        function initEventListeners() {
            // View mode toggle
            document.getElementById('gridView').addEventListener('change', () => loadCars());
            document.getElementById('tableView').addEventListener('change', () => loadCars());

            // Search and filter events
            document.getElementById('searchInput').addEventListener('input', debounce(() => loadCars(), 500));
            document.getElementById('statusFilter').addEventListener('change', () => loadCars());
            document.getElementById('brandFilter').addEventListener('change', () => loadCars());
            document.getElementById('yearFrom').addEventListener('input', debounce(() => loadCars(), 500));
            document.getElementById('yearTo').addEventListener('input', debounce(() => loadCars(), 500));
        }

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

        // Reset Filters
        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('brandFilter').value = '';
            document.getElementById('yearFrom').value = '';
            document.getElementById('yearTo').value = '';
            loadCars();
        }

        // Add Car
        document.getElementById('addCarForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'add_car');

            try {
                const response = await fetch('../../api/cars.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showToast('success', 'تمت إضافة السيارة بنجاح');
                    $('#addCarModal').modal('hide');
                    this.reset();
                    loadCars();
                    loadCarStats();
                } else {
                    showToast('error', data.message);
                }
            } catch (error) {
                console.error('Error adding car:', error);
                showToast('error', 'حدث خطأ أثناء إضافة السيارة');
            }
        });

        // Edit Car
        async function editCar(carId) {
            try {
                const response = await fetch(`../../api/cars.php?action=get_car&id=${carId}`);
                const data = await response.json();

                if (data.success) {
                    const car = data.car;
                    const form = `
                <form id="editCarForm" class="ajax-form" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="${car.id}">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">الماركة</label>
                                <input type="text" class="form-control glass-input" 
                                       name="brand" value="${car.brand}" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">الموديل</label>
                                <input type="text" class="form-control glass-input" 
                                       name="model" value="${car.model}" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">سنة الصنع</label>
                                <input type="number" class="form-control glass-input" 
                                       name="year" value="${car.year}" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">رقم اللوحة</label>
                                <input type="text" class="form-control glass-input" 
                                       name="plate_number" value="${car.plate_number}" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">اللون</label>
                                <input type="text" class="form-control glass-input" 
                                       name="color" value="${car.color}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">السعر اليومي (₪)</label>
                                <input type="number" step="0.01" class="form-control glass-input" 
                                       name="daily_rate" value="${car.daily_rate}" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">تكلفة الصيانة/يوم (₪)</label>
                                <input type="number" step="0.01" class="form-control glass-input" 
                                       name="maintenance_cost_per_day" value="${car.maintenance_cost_per_day}">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">حالة السيارة</label>
                                <select class="form-select glass-input" name="status">
                                    <option value="available" ${car.status === 'available' ? 'selected' : ''}>متاحة</option>
                                    <option value="rented" ${car.status === 'rented' ? 'selected' : ''}>مؤجرة</option>
                                    <option value="maintenance" ${car.status === 'maintenance' ? 'selected' : ''}>صيانة</option>
                                    <option value="reserved" ${car.status === 'reserved' ? 'selected' : ''}>محجوزة</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">المميزات</label>
                        <textarea class="form-control glass-input" name="features" rows="3">${car.features || ''}</textarea>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-orange">
                            <i class="fas fa-save me-2"></i> حفظ التعديلات
                        </button>
                    </div>
                </form>`;

                    document.getElementById('editCarFormContainer').innerHTML = form;
                    $('#editCarModal').modal('show');

                    // Add form submit event
                    document.getElementById('editCarForm').addEventListener('submit', async function (e) {
                        e.preventDefault();

                        const formData = new FormData(this);
                        formData.append('action', 'update_car');

                        try {
                            const response = await fetch('../../api/cars.php', {
                                method: 'POST',
                                body: formData
                            });

                            const data = await response.json();

                            if (data.success) {
                                showToast('success', 'تم تحديث السيارة بنجاح');
                                $('#editCarModal').modal('hide');
                                loadCars();
                                loadCarStats();
                            } else {
                                showToast('error', data.message);
                            }
                        } catch (error) {
                            console.error('Error updating car:', error);
                            showToast('error', 'حدث خطأ أثناء تحديث السيارة');
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading car details:', error);
            }
        }

        // Delete Car
        async function deleteCar(carId) {
            if (!confirm('هل أنت متأكد من حذف هذه السيارة؟')) return;

            try {
                const response = await fetch('../../api/cars.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_car&id=${carId}`
                });

                const data = await response.json();

                if (data.success) {
                    showToast('success', 'تم حذف السيارة بنجاح');
                    loadCars();
                    loadCarStats();
                } else {
                    showToast('error', data.message);
                }
            } catch (error) {
                console.error('Error deleting car:', error);
                showToast('error', 'حدث خطأ أثناء حذف السيارة');
            }
        }

        // View Car Details
        async function viewCarDetails(carId) {
            try {
                const response = await fetch(`../../api/cars.php?action=get_car_details&id=${carId}`);
                const data = await response.json();

                if (data.success) {
                    const car = data.car;
                    const details = `
                <div class="row">
                    <div class="col-md-5">
                        <div class="text-center">
                            <img src="${car.image || '../../assets/images/default-car.jpg'}" 
                                 alt="${car.brand} ${car.model}" 
                                 class="img-fluid rounded" style="max-height: 300px;">
                        </div>
                        
                        <div class="mt-3">
                            <h5>المعلومات الأساسية</h5>
                            <table class="table table-dark table-sm">
                                <tr>
                                    <td>رقم اللوحة:</td>
                                    <td><strong>${car.plate_number}</strong></td>
                                </tr>
                                <tr>
                                    <td>الحالة:</td>
                                    <td><span class="badge ${getStatusClass(car.status)}">${getStatusText(car.status)}</span></td>
                                </tr>
                                <tr>
                                    <td>تاريخ الإضافة:</td>
                                    <td>${car.created_at}</td>
                                </tr>
                                <tr>
                                    <td>آخر تحديث:</td>
                                    <td>${car.updated_at || 'لم يتم التحديث'}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="col-md-7">
                        <h4>${car.brand} ${car.model} ${car.year}</h4>
                        
                        <div class="row mt-4">
                            <div class="col-6 mb-3">
                                <div class="glass-card p-3 text-center">
                                    <small class="text-muted d-block">السعر اليومي</small>
                                    <h4 class="text-orange">${car.daily_rate} ₪</h4>
                                </div>
                            </div>
                            
                            <div class="col-6 mb-3">
                                <div class="glass-card p-3 text-center">
                                    <small class="text-muted d-block">تكلفة الصيانة/يوم</small>
                                    <h4>${car.maintenance_cost_per_day} ₪</h4>
                                </div>
                            </div>
                            
                            <div class="col-6 mb-3">
                                <div class="glass-card p-3 text-center">
                                    <small class="text-muted d-block">نوع التأمين</small>
                                    <h4>${car.insurance_type === 'full' ? 'كامل' : car.insurance_type === 'partial' ? 'جزئي' : 'ضد الغير'}</h4>
                                </div>
                            </div>
                            
                            <div class="col-6 mb-3">
                                <div class="glass-card p-3 text-center">
                                    <small class="text-muted d-block">اللون</small>
                                    <h4>${car.color || 'غير محدد'}</h4>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <h5>المميزات</h5>
                            <div class="glass-card p-3">
                                ${car.features ? car.features.split(',').map(f => `<span class="badge bg-info me-2 mb-2">${f.trim()}</span>`).join('') : 'لا توجد مميزات'}
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <h5>إحصائيات الحجوزات</h5>
                            <div class="row">
                                <div class="col-4 text-center">
                                    <div class="glass-card p-2">
                                        <small class="text-muted d-block">إجمالي الحجوزات</small>
                                        <h4 class="mb-0">${data.stats.total_bookings}</h4>
                                    </div>
                                </div>
                                
                                <div class="col-4 text-center">
                                    <div class="glass-card p-2">
                                        <small class="text-muted d-block">أيام التأجير</small>
                                        <h4 class="mb-0">${data.stats.total_days}</h4>
                                    </div>
                                </div>
                                
                                <div class="col-4 text-center">
                                    <div class="glass-card p-2">
                                        <small class="text-muted d-block">الإيرادات</small>
                                        <h4 class="mb-0">${data.stats.total_income} ₪</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;

                    document.getElementById('carDetailsContainer').innerHTML = details;
                    $('#carDetailsModal').modal('show');
                }
            } catch (error) {
                console.error('Error loading car details:', error);
            }
        }

        // Book Car
        function bookCar(carId) {
            window.location.href = `../bookings/create.php?car_id=${carId}`;
        }

        // Export Data
        async function exportData(format) {
            const search = document.getElementById('searchInput').value;
            const status = document.getElementById('statusFilter').value;
            const brand = document.getElementById('brandFilter').value;

            try {
                const response = await fetch(`../../api/cars.php?action=export&format=${format}&search=${search}&status=${status}&brand=${brand}`);

                if (format === 'print') {
                    // Open print window
                    const printWindow = window.open('', '_blank');
                    printWindow.document.write(`
                    <html>
                        <head>
                            <title>طباعة قائمة السيارات</title>
                            <style>
                                body { font-family: Arial, sans-serif; }
                                table { width: 100%; border-collapse: collapse; }
                                th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
                                th { background-color: #f2f2f2; }
                            </style>
                        </head>
                        <body>
                            <h2>قائمة السيارات</h2>
                            ${await response.text()}
                        </body>
                    </html>
                `);
                    printWindow.document.close();
                    printWindow.print();
                } else {
                    // Download file
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `cars_export.${format}`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }
            } catch (error) {
                console.error('Error exporting data:', error);
            }
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