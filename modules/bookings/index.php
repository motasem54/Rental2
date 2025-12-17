<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';
require_once '../../core/WhatsAppAPI.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الحجوزات</title>

    <link rel="stylesheet" href="../../assets/css/glassmorphism.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        .booking-card {
            border-left: 5px solid;
            transition: all 0.3s ease;
        }

        .booking-status-pending {
            border-left-color: #ffc107;
        }

        .booking-status-active {
            border-left-color: #28a745;
        }

        .booking-status-completed {
            border-left-color: #17a2b8;
        }

        .booking-status-cancelled {
            border-left-color: #dc3545;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--primary);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -20px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary);
            border: 2px solid white;
        }

        .calendar-day {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 2px;
            cursor: pointer;
        }

        .calendar-day.available {
            background: #28a745;
            color: white;
        }

        .calendar-day.booked {
            background: #dc3545;
            color: white;
        }

        .calendar-day.partial {
            background: #ffc107;
            color: black;
        }

        .cost-breakdown {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
        }

        .cost-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .cost-item.total {
            font-weight: bold;
            font-size: 1.1rem;
            border-top: 2px solid var(--primary);
            margin-top: 10px;
            padding-top: 10px;
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
                    <h2><i class="fas fa-calendar-check me-2"></i> إدارة الحجوزات</h2>
                    <small class="text-muted">إدارة جميع حجوزات تأجير السيارات</small>
                </div>

                <div>
                    <button class="btn btn-orange me-2" onclick="window.location.href='create.php'">
                        <i class="fas fa-plus me-2"></i> حجز جديد
                    </button>

                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-light dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-filter me-2"></i> تصفية
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="filterBookings('all')">جميع الحجوزات</a></li>
                            <li><a class="dropdown-item" href="#" onclick="filterBookings('pending')">قيد الانتظار</a>
                            </li>
                            <li><a class="dropdown-item" href="#" onclick="filterBookings('active')">نشطة</a></li>
                            <li><a class="dropdown-item" href="#" onclick="filterBookings('today')">حجوزات اليوم</a>
                            </li>
                            <li><a class="dropdown-item" href="#" onclick="filterBookings('upcoming')">القادمة</a></li>
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
                        <div class="number" id="pendingBookings">0</div>
                        <div class="label">قيد الانتظار</div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="glass-card stat-card">
                        <div class="card-icon bg-success-gradient mx-auto">
                            <i class="fas fa-play-circle"></i>
                        </div>
                        <div class="number" id="activeBookings">0</div>
                        <div class="label">نشطة حالياً</div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="glass-card stat-card">
                        <div class="card-icon bg-info-gradient mx-auto">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="number" id="completedBookings">0</div>
                        <div class="label">مكتملة</div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="glass-card stat-card">
                        <div class="card-icon bg-danger-gradient mx-auto">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="number" id="cancelledBookings">0</div>
                        <div class="label">ملغية</div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="glass-card p-3 mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control glass-input" id="searchBookings"
                            placeholder="بحث برقم الحجز أو العميل...">
                    </div>

                    <div class="col-md-2">
                        <select class="form-select glass-input" id="statusFilter">
                            <option value="">جميع الحالات</option>
                            <option value="pending">قيد الانتظار</option>
                            <option value="active">نشطة</option>
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
                        <button class="btn btn-outline-light w-100" onclick="resetBookingFilters()">
                            <i class="fas fa-redo"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bookings Table -->
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">قائمة الحجوزات</h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="autoRefreshBookings" checked>
                        <label class="form-check-label" for="autoRefreshBookings">تحديث تلقائي</label>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark table-hover" id="bookingsTable">
                        <thead>
                            <tr>
                                <th>رقم الحجز</th>
                                <th>العميل</th>
                                <th>السيارة</th>
                                <th>الفترة</th>
                                <th>المدة</th>
                                <th>المبلغ</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="bookingsTableBody">
                            <!-- Bookings will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation" class="mt-3">
                    <ul class="pagination justify-content-center" id="bookingsPagination">
                        <!-- Pagination will be loaded via AJAX -->
                    </ul>
                </nav>
            </div>

            <!-- Calendar View -->
            <div class="glass-card mt-4">
                <h5 class="mb-3"><i class="fas fa-calendar-alt me-2"></i> تقويم الحجوزات</h5>
                <div id="bookingsCalendar" style="min-height: 400px;"></div>
            </div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="modal fade" id="bookingDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content glass-card">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i> تفاصيل الحجز</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="bookingDetailsContainer">
                    <!-- Details will be loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="button" class="btn btn-primary" onclick="printInvoice()">
                        <i class="fas fa-print me-2"></i> طباعة الفاتورة
                    </button>
                    <button type="button" class="btn btn-success" onclick="sendWhatsAppReminder()">
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
                    <h5 class="modal-title"><i class="fas fa-sync-alt me-2"></i> تحديث حالة الحجز</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="updateStatusForm">
                        <input type="hidden" id="bookingId" name="booking_id">

                        <div class="mb-3">
                            <label class="form-label">الحالة الجديدة</label>
                            <select class="form-select glass-input" id="newStatus" name="status" required>
                                <option value="pending">قيد الانتظار</option>
                                <option value="active">نشط</option>
                                <option value="completed">مكتمل</option>
                                <option value="cancelled">ملغي</option>
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
            loadBookings();
            loadBookingStats();
            loadCarsForFilter();
            initCalendar();
            initFlatpickr();

            // Auto-refresh
            setInterval(() => {
                if (document.getElementById('autoRefreshBookings').checked) {
                    loadBookings();
                    loadBookingStats();
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

        // Load Bookings
        async function loadBookings(page = 1) {
            const search = document.getElementById('searchBookings').value;
            const status = document.getElementById('statusFilter').value;
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            const carId = document.getElementById('carFilter').value;

            try {
                const response = await fetch(`../../api/bookings.php?action=get_bookings&page=${page}&search=${search}&status=${status}&date_from=${dateFrom}&date_to=${dateTo}&car_id=${carId}`);
                const data = await response.json();

                if (data.success) {
                    updateBookingsTable(data.bookings);
                    updateBookingsPagination(data.pagination);
                    updateCalendar(data.bookings);
                }
            } catch (error) {
                console.error('Error loading bookings:', error);
            }
        }

        // Load Booking Statistics
        async function loadBookingStats() {
            try {
                const response = await fetch('../../api/bookings.php?action=get_stats');
                const data = await response.json();

                if (data.success) {
                    document.getElementById('pendingBookings').textContent = data.stats.pending;
                    document.getElementById('activeBookings').textContent = data.stats.active;
                    document.getElementById('completedBookings').textContent = data.stats.completed;
                    document.getElementById('cancelledBookings').textContent = data.stats.cancelled;
                }
            } catch (error) {
                console.error('Error loading booking stats:', error);
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

        // Update Bookings Table
        function updateBookingsTable(bookings) {
            let html = '';

            if (bookings.length === 0) {
                html = `
            <tr>
                <td colspan="8" class="text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد حجوزات</h5>
                </td>
            </tr>`;
            } else {
                bookings.forEach(booking => {
                    const statusClass = getBookingStatusClass(booking.status);
                    const statusText = getBookingStatusText(booking.status);

                    html += `
                <tr class="booking-row" data-booking-id="${booking.id}">
                    <td>
                        <strong>#${booking.id}</strong><br>
                        <small class="text-muted">${booking.created_at}</small>
                    </td>
                    <td>
                        <strong>${booking.customer_name}</strong><br>
                        <small class="text-muted">${booking.customer_phone}</small>
                    </td>
                    <td>
                        ${booking.car_brand} ${booking.car_model}<br>
                        <small class="text-muted">${booking.car_plate}</small>
                    </td>
                    <td>
                        ${booking.start_date} إلى ${booking.end_date}<br>
                        <small class="text-muted">${booking.days} يوم</small>
                    </td>
                    <td>${booking.days} يوم</td>
                    <td>
                        <strong class="text-orange">${booking.total} ₪</strong><br>
                        <small class="text-muted">${booking.paid ? 'مدفوع' : 'غير مدفوع'}</small>
                    </td>
                    <td>
                        <span class="badge ${statusClass}">${statusText}</span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-info me-1" onclick="viewBookingDetails(${booking.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning me-1" onclick="updateBookingStatus(${booking.id}, '${booking.status}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="sendBookingWhatsApp(${booking.id})">
                            <i class="fab fa-whatsapp"></i>
                        </button>
                    </td>
                </tr>`;
                });
            }

            document.getElementById('bookingsTableBody').innerHTML = html;
        }

        // Update Pagination
        function updateBookingsPagination(pagination) {
            let html = '';

            if (pagination.pages > 1) {
                // Previous button
                html += `
            <li class="page-item ${pagination.current_page <= 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadBookings(${pagination.current_page - 1})">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>`;

                // Page numbers
                for (let i = 1; i <= pagination.pages; i++) {
                    if (i === pagination.current_page) {
                        html += `<li class="page-item active"><a class="page-link" href="#">${i}</a></li>`;
                    } else if (i >= pagination.current_page - 2 && i <= pagination.current_page + 2) {
                        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadBookings(${i})">${i}</a></li>`;
                    }
                }

                // Next button
                html += `
            <li class="page-item ${pagination.current_page >= pagination.pages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadBookings(${pagination.current_page + 1})">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>`;
            }

            document.getElementById('bookingsPagination').innerHTML = html;
        }

        // Get Booking Status Class
        function getBookingStatusClass(status) {
            const classMap = {
                'pending': 'bg-warning',
                'active': 'bg-success',
                'completed': 'bg-info',
                'cancelled': 'bg-danger'
            };
            return classMap[status] || 'bg-secondary';
        }

        // Get Booking Status Text
        function getBookingStatusText(status) {
            const textMap = {
                'pending': 'قيد الانتظار',
                'active': 'نشط',
                'completed': 'مكتمل',
                'cancelled': 'ملغي'
            };
            return textMap[status] || status;
        }

        // Initialize Calendar
        function initCalendar() {
            const calendarEl = document.getElementById('bookingsCalendar');
            window.calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'ar',
                direction: 'rtl',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: [],
                eventClick: function (info) {
                    viewBookingDetails(info.event.id);
                }
            });

            window.calendar.render();
        }

        // Update Calendar with Bookings
        function updateCalendar(bookings) {
            const events = bookings.map(booking => ({
                id: booking.id,
                title: `${booking.car_brand} - ${booking.customer_name}`,
                start: booking.start_date,
                end: booking.end_date,
                backgroundColor: getBookingColor(booking.status),
                borderColor: getBookingColor(booking.status),
                extendedProps: {
                    status: booking.status,
                    total: booking.total
                }
            }));

            window.calendar.removeAllEvents();
            window.calendar.addEventSource(events);
        }

        // Get Booking Color
        function getBookingColor(status) {
            const colorMap = {
                'pending': '#ffc107',
                'active': '#28a745',
                'completed': '#17a2b8',
                'cancelled': '#dc3545'
            };
            return colorMap[status] || '#6c757d';
        }

        // Filter Bookings
        function filterBookings(filter) {
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

            loadBookings();
        }

        // Reset Filters
        function resetBookingFilters() {
            document.getElementById('searchBookings').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('dateFrom').value = '';
            document.getElementById('dateTo').value = '';
            document.getElementById('carFilter').value = '';
            loadBookings();
        }

        // View Booking Details
        async function viewBookingDetails(bookingId) {
            try {
                const response = await fetch(`../../api/bookings.php?action=get_booking_details&id=${bookingId}`);
                const data = await response.json();

                if (data.success) {
                    const booking = data.booking;
                    const details = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="glass-card p-3 mb-3">
                            <h5><i class="fas fa-user me-2"></i> معلومات العميل</h5>
                            <table class="table table-sm table-dark">
                                <tr>
                                    <td>اسم العميل:</td>
                                    <td><strong>${booking.customer_name}</strong></td>
                                </tr>
                                <tr>
                                    <td>رقم الهاتف:</td>
                                    <td>${booking.customer_phone}</td>
                                </tr>
                                <tr>
                                    <td>البريد الإلكتروني:</td>
                                    <td>${booking.customer_email}</td>
                                </tr>
                                <tr>
                                    <td>رقم الرخصة:</td>
                                    <td>${booking.customer_license}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="glass-card p-3">
                            <h5><i class="fas fa-car me-2"></i> معلومات السيارة</h5>
                            <table class="table table-sm table-dark">
                                <tr>
                                    <td>السيارة:</td>
                                    <td><strong>${booking.car_brand} ${booking.car_model} ${booking.car_year}</strong></td>
                                </tr>
                                <tr>
                                    <td>رقم اللوحة:</td>
                                    <td>${booking.car_plate}</td>
                                </tr>
                                <tr>
                                    <td>اللون:</td>
                                    <td>${booking.car_color}</td>
                                </tr>
                                <tr>
                                    <td>نوع التأمين:</td>
                                    <td>${booking.insurance_type === 'full' ? 'كامل' : booking.insurance_type === 'partial' ? 'جزئي' : 'ضد الغير'}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="glass-card p-3 mb-3">
                            <h5><i class="fas fa-calendar-alt me-2"></i> تفاصيل الحجز</h5>
                            <table class="table table-sm table-dark">
                                <tr>
                                    <td>رقم الحجز:</td>
                                    <td><strong>#${booking.id}</strong></td>
                                </tr>
                                <tr>
                                    <td>تاريخ الحجز:</td>
                                    <td>${booking.created_at}</td>
                                </tr>
                                <tr>
                                    <td>الفترة:</td>
                                    <td>${booking.start_date} إلى ${booking.end_date}</td>
                                </tr>
                                <tr>
                                    <td>المدة:</td>
                                    <td>${booking.days} يوم</td>
                                </tr>
                                <tr>
                                    <td>الحالة:</td>
                                    <td><span class="badge ${getBookingStatusClass(booking.status)}">${getBookingStatusText(booking.status)}</span></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="glass-card p-3">
                            <h5><i class="fas fa-money-bill-wave me-2"></i> التفاصيل المالية</h5>
                            <div class="cost-breakdown">
                                <div class="cost-item">
                                    <span>السعر اليومي:</span>
                                    <span>${booking.daily_rate} ₪ × ${booking.days} يوم</span>
                                </div>
                                <div class="cost-item">
                                    <span>تكلفة الصيانة:</span>
                                    <span>${booking.maintenance_cost} ₪</span>
                                </div>
                                <div class="cost-item">
                                    <span>تكلفة التأمين:</span>
                                    <span>${booking.insurance_cost} ₪</span>
                                </div>
                                <div class="cost-item">
                                    <span>الضريبة (${booking.tax_rate}%):</span>
                                    <span>${booking.tax} ₪</span>
                                </div>
                                <div class="cost-item total">
                                    <span>الإجمالي:</span>
                                    <span>${booking.total} ₪</span>
                                </div>
                                <div class="cost-item">
                                    <span>الدفعة المقدمة:</span>
                                    <span>${booking.deposit} ₪</span>
                                </div>
                                <div class="cost-item">
                                    <span>المتبقي:</span>
                                    <span>${booking.remaining} ₪</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;

                    document.getElementById('bookingDetailsContainer').innerHTML = details;
                    window.currentBookingId = bookingId;
                    $('#bookingDetailsModal').modal('show');
                }
            } catch (error) {
                console.error('Error loading booking details:', error);
            }
        }

        // Update Booking Status
        function updateBookingStatus(bookingId, currentStatus) {
            document.getElementById('bookingId').value = bookingId;
            document.getElementById('newStatus').value = currentStatus;
            $('#updateStatusModal').modal('show');
        }

        // Send WhatsApp Reminder
        async function sendBookingWhatsApp(bookingId) {
            if (!confirm('هل تريد إرسال تذكير عبر واتساب؟')) return;

            try {
                const response = await fetch('../../api/whatsapp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=send_booking_reminder&booking_id=${bookingId}`
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

        // Print Invoice
        function printInvoice() {
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
            <html>
                <head>
                    <title>فاتورة الحجز #${window.currentBookingId}</title>
                    <style>
                        body { font-family: Arial, sans-serif; direction: rtl; padding: 20px; }
                        .invoice-header { text-align: center; margin-bottom: 30px; }
                        .invoice-details { margin-bottom: 20px; }
                        .invoice-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                        .invoice-table th, .invoice-table td { border: 1px solid #ddd; padding: 8px; text-align: center; }
                        .invoice-table th { background-color: #f2f2f2; }
                        .total-row { font-weight: bold; background-color: #f8f9fa; }
                    </style>
                </head>
                <body>
                    <div class="invoice-header">
                        <h2>فاتورة تأجير سيارة</h2>
                        <h3>رقم الحجز: #${window.currentBookingId}</h3>
                    </div>
                    ${document.getElementById('bookingDetailsContainer').innerHTML}
                </body>
            </html>
        `);
            printWindow.document.close();
            printWindow.print();
        }

        // Event Listeners
        document.getElementById('searchBookings').addEventListener('input', debounce(() => loadBookings(), 500));
        document.getElementById('statusFilter').addEventListener('change', () => loadBookings());
        document.getElementById('dateFrom').addEventListener('change', () => loadBookings());
        document.getElementById('dateTo').addEventListener('change', () => loadBookings());
        document.getElementById('carFilter').addEventListener('change', () => loadBookings());

        // Update Status Form Submit
        document.getElementById('updateStatusForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'update_booking_status');

            try {
                const response = await fetch('../../api/bookings.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showToast('success', 'تم تحديث حالة الحجز بنجاح');
                    $('#updateStatusModal').modal('hide');
                    loadBookings();
                    loadBookingStats();
                } else {
                    document.getElementById('statusUpdateResponse').innerHTML =
                        `<div class="alert alert-danger">${data.message}</div>`;
                }
            } catch (error) {
                console.error('Error updating status:', error);
                document.getElementById('statusUpdateResponse').innerHTML =
                    '<div class="alert alert-danger">حدث خطأ أثناء تحديث الحالة</div>';
            }
        });

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