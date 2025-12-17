<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$pageTitle = "إنشاء عملية تأجير جديدة - نظام تأجير السيارات";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>

<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-file-contract me-2"></i> إنشاء عملية تأجير جديدة</h2>
                    <small class="text-muted">قم بتعبئة جميع البيانات المطلوبة لإنشاء عملية تأجير جديدة</small>
                </div>
                <div>
                    <button class="btn btn-secondary" onclick="window.location.href='index.php'">
                        <i class="fas fa-arrow-right me-2"></i> العودة للقائمة
                    </button>
                </div>
            </div>

            <!-- Rental Form -->
            <form id="rentalForm" class="ajax-form" data-action="../../api/rentals.php?action=create_rental">
                <div class="row">
                    <!-- Customer & Car Selection -->
                    <div class="col-lg-6 mb-4">
                        <div class="glass-card">
                            <h5 class="mb-3"><i class="fas fa-user-tie me-2"></i> بيانات العميل والسيارة</h5>

                            <div class="mb-3">
                                <label class="form-label">العميل <span class="text-danger">*</span></label>
                                <select class="form-select glass-input" id="customer_id" name="customer_id" required>
                                    <option value="">اختر العميل...</option>
                                </select>
                                <small class="text-muted">أو <a href="../customers/add.php" target="_blank">إضافة عميل
                                        جديد</a></small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">السيارة <span class="text-danger">*</span></label>
                                <select class="form-select glass-input" id="car_id" name="car_id" required>
                                    <option value="">اختر السيارة...</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">نوع التأجير <span class="text-danger">*</span></label>
                                <select class="form-select glass-input" id="rental_type" name="rental_type" required>
                                    <option value="daily">يومي</option>
                                    <option value="weekly">أسبوعي</option>
                                    <option value="monthly">شهري</option>
                                    <option value="long_term">طويل الأمد</option>
                                </select>
                            </div>

                            <div id="carDetails" class="alert alert-info" style="display: none;">
                                <h6>معلومات السيارة المختارة:</h6>
                                <div id="carDetailsContent"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Rental Period -->
                    <div class="col-lg-6 mb-4">
                        <div class="glass-card">
                            <h5 class="mb-3"><i class="fas fa-calendar-alt me-2"></i> فترة التأجير</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">تاريخ البداية <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control glass-input" id="start_date"
                                        name="start_date" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">وقت الاستلام</label>
                                    <input type="time" class="form-control glass-input" id="pickup_time"
                                        name="pickup_time" value="14:00">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">تاريخ النهاية <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control glass-input" id="end_date" name="end_date"
                                        required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">وقت الإرجاع</label>
                                    <input type="time" class="form-control glass-input" id="return_time"
                                        name="return_time" value="12:00">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">عدد الأيام</label>
                                <input type="number" class="form-control glass-input" id="rental_days"
                                    name="rental_days" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Mileage & Condition -->
                    <div class="col-lg-6 mb-4">
                        <div class="glass-card">
                            <h5 class="mb-3"><i class="fas fa-tachometer-alt me-2"></i> الكيلومترات والحالة</h5>

                            <div class="mb-3">
                                <label class="form-label">الكيلومترات المسموحة</label>
                                <input type="number" class="form-control glass-input" id="km_allowed" name="km_allowed"
                                    placeholder="مثال: 1000">
                                <small class="text-muted">اترك فارغاً لعدد غير محدود</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">سعر الكيلومتر الإضافي (₪)</label>
                                <input type="number" step="0.01" class="form-control glass-input" id="extra_km_rate"
                                    name="extra_km_rate" value="2.00">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">قراءة العداد عند الاستلام <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control glass-input" id="pickup_km" name="pickup_km"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">حالة السيارة عند الاستلام</label>
                                <textarea class="form-control glass-input" id="pickup_condition" name="pickup_condition"
                                    rows="3" placeholder="صف حالة السيارة بالتفصيل..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Details -->
                    <div class="col-lg-6 mb-4">
                        <div class="glass-card">
                            <h5 class="mb-3"><i class="fas fa-money-bill-wave me-2"></i> التفاصيل المالية</h5>

                            <div class="mb-3">
                                <label class="form-label">السعر اليومي (₪)</label>
                                <input type="number" step="0.01" class="form-control glass-input" id="daily_rate"
                                    name="daily_rate" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">إجمالي قيمة التأجير (₪)</label>
                                <input type="number" step="0.01" class="form-control glass-input"
                                    id="total_rental_amount" name="total_rental_amount" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">قيمة التأمين (₪) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control glass-input" id="deposit_amount"
                                    name="deposit_amount" required>
                                <small class="text-muted">عادة 40% من الإجمالي</small>
                            </div>

                            <div class="alert alert-warning">
                                <h6>ملخص التكلفة:</h6>
                                <div id="costSummary">
                                    <p class="mb-1">السعر اليومي: <span id="summaryDailyRate">0</span> ₪</p>
                                    <p class="mb-1">عدد الأيام: <span id="summaryDays">0</span></p>
                                    <hr>
                                    <p class="mb-1"><strong>الإجمالي: <span id="summaryTotal">0</span> ₪</strong></p>
                                    <p class="mb-0">التأمين: <span id="summaryDeposit">0</span> ₪</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Options -->
                    <div class="col-lg-12 mb-4">
                        <div class="glass-card">
                            <h5 class="mb-3"><i class="fas fa-cog me-2"></i> خيارات إضافية</h5>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="contract_signed"
                                            name="contract_signed" value="1">
                                        <label class="form-check-label" for="contract_signed">
                                            العقد موقّع
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="requires_verification"
                                            name="requires_verification" value="1">
                                        <label class="form-check-label" for="requires_verification">
                                            يتطلب تحقق
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">حالة العملية</label>
                                    <select class="form-select glass-input" id="rental_status" name="rental_status">
                                        <option value="pending">قيد الانتظار</option>
                                        <option value="active">نشطة</option>
                                        <option value="completed">مكتملة</option>
                                        <option value="cancelled">ملغية</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ملاحظات إضافية</label>
                                <textarea class="form-control glass-input" id="calculation_notes"
                                    name="calculation_notes" rows="3"
                                    placeholder="أي ملاحظات أو تفاصيل إضافية..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="col-lg-12">
                        <div class="glass-card">
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary"
                                    onclick="window.location.href='index.php'">
                                    <i class="fas fa-times me-2"></i> إلغاء
                                </button>
                                <div>
                                    <button type="submit" class="btn btn-orange">
                                        <i class="fas fa-save me-2"></i> حفظ عملية التأجير
                                    </button>
                                    <button type="button" class="btn btn-success ms-2" id="saveAndPrint">
                                        <i class="fas fa-print me-2"></i> حفظ وطباعة العقد
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="../../assets/js/ajax-handler.js"></script>
    <script src="../../assets/js/app.js"></script>

    <script>
        // Initialize
        document.addEventListener('DOMContentLoaded', function () {
            loadCustomers();
            loadCars();
            initializeCalculations();
            initializeSelect2();

            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('start_date').setAttribute('min', today);
            document.getElementById('end_date').setAttribute('min', today);
        });

        // Initialize Select2
        function initializeSelect2() {
            $('#customer_id, #car_id').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        }

        // Load Customers
        async function loadCustomers() {
            try {
                const response = await fetch('../../api/customers.php?action=get_customers');
                const data = await response.json();

                if (data.success) {
                    const select = document.getElementById('customer_id');
                    data.customers.forEach(customer => {
                        const option = document.createElement('option');
                        option.value = customer.id;
                        option.textContent = `${customer.full_name} - ${customer.phone}`;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading customers:', error);
            }
        }

        // Load Cars
        async function loadCars() {
            try {
                const response = await fetch('../../api/cars.php?action=get_cars&status=available');
                const data = await response.json();

                if (data.success) {
                    const select = document.getElementById('car_id');
                    data.cars.forEach(car => {
                        const option = document.createElement('option');
                        option.value = car.id;
                        option.textContent = `${car.brand} ${car.model} - ${car.plate_number} (${car.daily_rate} ₪/يوم)`;
                        option.dataset.dailyRate = car.daily_rate;
                        option.dataset.details = JSON.stringify(car);
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading cars:', error);
            }
        }

        // Initialize Calculations
        function initializeCalculations() {
            // Car selection change
            document.getElementById('car_id').addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    const dailyRate = selectedOption.dataset.dailyRate;
                    const carDetails = JSON.parse(selectedOption.dataset.details);

                    document.getElementById('daily_rate').value = dailyRate;

                    // Show car details
                    const detailsDiv = document.getElementById('carDetails');
                    const detailsContent = document.getElementById('carDetailsContent');
                    detailsContent.innerHTML = `
                        <p class="mb-1"><strong>الماركة:</strong> ${carDetails.brand} ${carDetails.model}</p>
                        <p class="mb-1"><strong>اللوحة:</strong> ${carDetails.plate_number}</p>
                        <p class="mb-1"><strong>اللون:</strong> ${carDetails.color}</p>
                        <p class="mb-0"><strong>السعر اليومي:</strong> ${carDetails.daily_rate} ₪</p>
                    `;
                    detailsDiv.style.display = 'block';

                    calculateTotal();
                }
            });

            // Date changes
            document.getElementById('start_date').addEventListener('change', calculateDays);
            document.getElementById('end_date').addEventListener('change', calculateDays);

            // Deposit change
            document.getElementById('deposit_amount').addEventListener('input', updateSummary);
        }

        // Calculate Days
        function calculateDays() {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);

            if (startDate && endDate && endDate >= startDate) {
                const days = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
                document.getElementById('rental_days').value = days;
                calculateTotal();
            }
        }

        // Calculate Total
        function calculateTotal() {
            const days = parseInt(document.getElementById('rental_days').value) || 0;
            const dailyRate = parseFloat(document.getElementById('daily_rate').value) || 0;

            if (days > 0 && dailyRate > 0) {
                const total = days * dailyRate;
                document.getElementById('total_rental_amount').value = total.toFixed(2);

                // Suggest deposit (40% of total)
                const suggestedDeposit = (total * 0.4).toFixed(2);
                if (!document.getElementById('deposit_amount').value) {
                    document.getElementById('deposit_amount').value = suggestedDeposit;
                }

                updateSummary();
            }
        }

        // Update Summary
        function updateSummary() {
            const dailyRate = parseFloat(document.getElementById('daily_rate').value) || 0;
            const days = parseInt(document.getElementById('rental_days').value) || 0;
            const total = parseFloat(document.getElementById('total_rental_amount').value) || 0;
            const deposit = parseFloat(document.getElementById('deposit_amount').value) || 0;

            document.getElementById('summaryDailyRate').textContent = dailyRate.toFixed(2);
            document.getElementById('summaryDays').textContent = days;
            document.getElementById('summaryTotal').textContent = total.toFixed(2);
            document.getElementById('summaryDeposit').textContent = deposit.toFixed(2);
        }

        // Save and Print
        document.getElementById('saveAndPrint').addEventListener('click', function () {
            const form = document.getElementById('rentalForm');
            if (form.checkValidity()) {
                // Add a flag to indicate print after save
                const printInput = document.createElement('input');
                printInput.type = 'hidden';
                printInput.name = 'print_after_save';
                printInput.value = '1';
                form.appendChild(printInput);

                form.dispatchEvent(new Event('submit'));
            } else {
                form.reportValidity();
            }
        });

        // Handle form submission success
        document.getElementById('rentalForm').addEventListener('ajax-success', function (e) {
            if (e.detail.data && e.detail.data.rental_id) {
                const rentalId = e.detail.data.rental_id;

                // Check if print was requested
                if (document.querySelector('input[name="print_after_save"]')) {
                    window.open(`contract.php?id=${rentalId}`, '_blank');
                }

                // Redirect to rentals list after a short delay
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2000);
            }
        });
    </script>
</body>

</html>