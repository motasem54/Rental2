<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$db = Database::getInstance()->getConnection();
$pageTitle = 'التقارير المالية';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - نظام تأجير السيارات</title>

    <link rel="stylesheet" href="../../assets/css/glassmorphism.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/rtl.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        .financial-card {
            background: linear-gradient(135deg, rgba(255, 87, 34, 0.1) 0%, rgba(18, 18, 18, 0.1) 100%);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid var(--glass-border);
        }

        .financial-stat {
            text-align: center;
            padding: 20px;
        }

        .financial-stat .icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.8rem;
        }

        .financial-stat .amount {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .financial-stat .label {
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .financial-stat .change {
            font-size: 0.85rem;
            margin-top: 8px;
        }

        .income-expense-chart {
            position: relative;
            height: 400px;
        }

        .transaction-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border-right: 4px solid;
            transition: all 0.3s ease;
        }

        .transaction-item:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(-5px);
        }

        .transaction-item.income {
            border-right-color: #28a745;
        }

        .transaction-item.expense {
            border-right-color: #dc3545;
        }

        .profit-margin {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            color: white;
        }

        .filter-section {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
    </style>
</head>

<body>
    <?php
    $currentDir = 'reports';
    include '../../includes/sidebar.php';
    ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-chart-line me-2"></i><?php echo $pageTitle; ?></h2>
                    <small class="text-muted">تقارير مفصلة عن الإيرادات والمصروفات والأرباح</small>
                </div>

                <div>
                    <button class="btn btn-orange me-2" onclick="exportFinancialReport('pdf')">
                        <i class="fas fa-file-pdf me-2"></i>تصدير PDF
                    </button>
                    <button class="btn btn-outline-light" onclick="exportFinancialReport('excel')">
                        <i class="fas fa-file-excel me-2"></i>تصدير Excel
                    </button>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">الفترة الزمنية</label>
                        <select class="form-select glass-input" id="timePeriod" onchange="loadFinancialData()">
                            <option value="today">اليوم</option>
                            <option value="week">هذا الأسبوع</option>
                            <option value="month" selected>هذا الشهر</option>
                            <option value="quarter">هذا الربع</option>
                            <option value="year">هذه السنة</option>
                            <option value="custom">مخصص</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" class="form-control glass-input" id="dateFrom"
                            value="<?php echo date('Y-m-01'); ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" class="form-control glass-input" id="dateTo"
                            value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-orange w-100" onclick="loadFinancialData()">
                            <i class="fas fa-sync-alt me-2"></i>تحديث
                        </button>
                    </div>
                </div>
            </div>

            <!-- Financial Summary Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="financial-card">
                        <div class="financial-stat">
                            <div class="icon bg-success-gradient">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                            <div class="amount text-success" id="totalIncome">0 ₪</div>
                            <div class="label">إجمالي الإيرادات</div>
                            <div class="change text-success" id="incomeChange">+0% عن الشهر الماضي</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="financial-card">
                        <div class="financial-stat">
                            <div class="icon bg-danger-gradient">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                            <div class="amount text-danger" id="totalExpenses">0 ₪</div>
                            <div class="label">إجمالي المصروفات</div>
                            <div class="change text-danger" id="expensesChange">+0% عن الشهر الماضي</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="financial-card">
                        <div class="financial-stat">
                            <div class="icon bg-primary-gradient">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="amount text-primary" id="netProfit">0 ₪</div>
                            <div class="label">صافي الربح</div>
                            <div class="change text-info" id="profitChange">+0% عن الشهر الماضي</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="profit-margin">
                        <h5 class="mb-2">هامش الربح</h5>
                        <h2 class="mb-1" id="profitMargin">0%</h2>
                        <small>من إجمالي الإيرادات</small>
                    </div>
                </div>
            </div>

            <!-- Income vs Expenses Chart -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="glass-card">
                        <h5 class="mb-3"><i class="fas fa-chart-bar me-2"></i>الإيرادات مقابل المصروفات</h5>
                        <div class="income-expense-chart">
                            <canvas id="incomeExpenseChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="glass-card h-100">
                        <h5 class="mb-3"><i class="fas fa-chart-pie me-2"></i>توزيع المصروفات</h5>
                        <div class="income-expense-chart">
                            <canvas id="expensesDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Breakdown -->
            <div class="row">
                <!-- Income Sources -->
                <div class="col-lg-6 mb-4">
                    <div class="glass-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="fas fa-coins me-2 text-success"></i>مصادر الإيرادات</h5>
                            <span class="badge bg-success" id="incomeCount">0 عملية</span>
                        </div>

                        <div id="incomeSourcesList">
                            <!-- Income items will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Expense Categories -->
                <div class="col-lg-6 mb-4">
                    <div class="glass-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="fas fa-receipt me-2 text-danger"></i>فئات المصروفات</h5>
                            <span class="badge bg-danger" id="expenseCount">0 عملية</span>
                        </div>

                        <div id="expenseCategoriesList">
                            <!-- Expense items will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Trend -->
            <div class="glass-card mb-4">
                <h5 class="mb-3"><i class="fas fa-calendar-alt me-2"></i>الاتجاه الشهري للأرباح</h5>
                <div style="height: 300px;">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>

            <!-- Detailed Transactions Table -->
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>سجل المعاملات المالية</h5>
                    <button class="btn btn-sm btn-outline-light" onclick="refreshTransactions()">
                        <i class="fas fa-sync-alt me-1"></i>تحديث
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark table-hover" id="transactionsTable">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>النوع</th>
                                <th>الفئة</th>
                                <th>الوصف</th>
                                <th>المبلغ</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="transactionsBody">
                            <!-- Transactions will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../includes/modal.php'; ?>

    <!-- JavaScript Files -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="../../assets/js/ajax-handler.js"></script>
    <script src="../../assets/js/app.js"></script>

    <script>
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function () {
            loadFinancialData();
            initCharts();
            initDatePickers();
        });

        // Initialize Date Pickers
        function initDatePickers() {
            flatpickr("#dateFrom", {
                locale: "ar",
                dateFormat: "Y-m-d"
            });

            flatpickr("#dateTo", {
                locale: "ar",
                dateFormat: "Y-m-d"
            });
        }

        // Load Financial Data
        async function loadFinancialData() {
            const period = document.getElementById('timePeriod').value;
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;

            try {
                const response = await fetch(`../../api/reports.php?action=financial_summary&period=${period}&from=${dateFrom}&to=${dateTo}`);
                const data = await response.json();

                if (data.success) {
                    updateFinancialSummary(data.summary);
                    updateIncomeSources(data.income_sources);
                    updateExpenseCategories(data.expense_categories);
                    updateTransactions(data.transactions);
                    updateCharts(data.chart_data);
                }
            } catch (error) {
                console.error('Error loading financial data:', error);
                showToast('حدث خطأ أثناء تحميل البيانات المالية', 'error');
            }
        }

        // Update Financial Summary
        function updateFinancialSummary(summary) {
            document.getElementById('totalIncome').textContent = formatCurrency(summary.total_income);
            document.getElementById('totalExpenses').textContent = formatCurrency(summary.total_expenses);
            document.getElementById('netProfit').textContent = formatCurrency(summary.net_profit);
            document.getElementById('profitMargin').textContent = summary.profit_margin + '%';

            document.getElementById('incomeChange').textContent =
                summary.income_change > 0 ? `+${summary.income_change}% عن الشهر الماضي` : `${summary.income_change}% عن الشهر الماضي`;

            document.getElementById('expensesChange').textContent =
                summary.expenses_change > 0 ? `+${summary.expenses_change}% عن الشهر الماضي` : `${summary.expenses_change}% عن الشهر الماضي`;

            document.getElementById('profitChange').textContent =
                summary.profit_change > 0 ? `+${summary.profit_change}% عن الشهر الماضي` : `${summary.profit_change}% عن الشهر الماضي`;
        }

        // Update Income Sources
        function updateIncomeSources(sources) {
            const container = document.getElementById('incomeSourcesList');
            let html = '';
            let totalCount = 0;

            sources.forEach(source => {
                totalCount += parseInt(source.count);
                html += `
            <div class="transaction-item income">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">${source.name}</h6>
                        <small class="text-muted">${source.count} عملية</small>
                    </div>
                    <div class="text-end">
                        <h5 class="mb-0 text-success">${formatCurrency(source.amount)}</h5>
                        <small class="text-muted">${source.percentage}%</small>
                    </div>
                </div>
            </div>`;
            });

            container.innerHTML = html;
            document.getElementById('incomeCount').textContent = totalCount + ' عملية';
        }

        // Update Expense Categories
        function updateExpenseCategories(categories) {
            const container = document.getElementById('expenseCategoriesList');
            let html = '';
            let totalCount = 0;

            categories.forEach(category => {
                totalCount += parseInt(category.count);
                html += `
            <div class="transaction-item expense">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">${category.name}</h6>
                        <small class="text-muted">${category.count} عملية</small>
                    </div>
                    <div class="text-end">
                        <h5 class="mb-0 text-danger">${formatCurrency(category.amount)}</h5>
                        <small class="text-muted">${category.percentage}%</small>
                    </div>
                </div>
            </div>`;
            });

            container.innerHTML = html;
            document.getElementById('expenseCount').textContent = totalCount + ' عملية';
        }

        // Update Transactions Table
        function updateTransactions(transactions) {
            const tbody = document.getElementById('transactionsBody');
            let html = '';

            transactions.forEach(transaction => {
                const typeClass = transaction.type === 'income' ? 'success' : 'danger';
                const typeIcon = transaction.type === 'income' ? 'arrow-up' : 'arrow-down';
                const typeLabel = transaction.type === 'income' ? 'إيراد' : 'مصروف';

                html += `
            <tr>
                <td>${transaction.date}</td>
                <td>
                    <span class="badge bg-${typeClass}">
                        <i class="fas fa-${typeIcon} me-1"></i>${typeLabel}
                    </span>
                </td>
                <td>${transaction.category}</td>
                <td>${transaction.description}</td>
                <td class="text-${typeClass} fw-bold">${formatCurrency(transaction.amount)}</td>
                <td>
                    <span class="badge bg-${transaction.status === 'completed' ? 'success' : 'warning'}">
                        ${transaction.status === 'completed' ? 'مكتمل' : 'معلق'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-light" onclick="viewTransaction(${transaction.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>`;
            });

            tbody.innerHTML = html;

            // Initialize DataTable
            if ($.fn.DataTable.isDataTable('#transactionsTable')) {
                $('#transactionsTable').DataTable().destroy();
            }

            $('#transactionsTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
                },
                order: [[0, 'desc']]
            });
        }

        // Initialize Charts
        function initCharts() {
            // Income vs Expenses Chart
            const ctx1 = document.getElementById('incomeExpenseChart').getContext('2d');
            window.incomeExpenseChart = new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'الإيرادات',
                            data: [],
                            backgroundColor: 'rgba(40, 167, 69, 0.7)',
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'المصروفات',
                            data: [],
                            backgroundColor: 'rgba(220, 53, 69, 0.7)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: 'white' }
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
                            grid: { color: 'rgba(255, 255, 255, 0.1)' }
                        },
                        x: {
                            ticks: { color: 'white' },
                            grid: { color: 'rgba(255, 255, 255, 0.1)' }
                        }
                    }
                }
            });

            // Expenses Distribution Chart
            const ctx2 = document.getElementById('expensesDistributionChart').getContext('2d');
            window.expensesDistributionChart = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [
                            '#FF5722',
                            '#dc3545',
                            '#fd7e14',
                            '#ffc107',
                            '#28a745',
                            '#17a2b8'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: 'white', padding: 15 }
                        }
                    }
                }
            });

            // Monthly Trend Chart
            const ctx3 = document.getElementById('monthlyTrendChart').getContext('2d');
            window.monthlyTrendChart = new Chart(ctx3, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'صافي الربح',
                        data: [],
                        borderColor: '#FF5722',
                        backgroundColor: 'rgba(255, 87, 34, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: 'white' }
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
                            grid: { color: 'rgba(255, 255, 255, 0.1)' }
                        },
                        x: {
                            ticks: { color: 'white' },
                            grid: { color: 'rgba(255, 255, 255, 0.1)' }
                        }
                    }
                }
            });
        }

        // Update Charts with new data
        function updateCharts(chartData) {
            if (chartData.income_expense && window.incomeExpenseChart) {
                window.incomeExpenseChart.data.labels = chartData.income_expense.labels;
                window.incomeExpenseChart.data.datasets[0].data = chartData.income_expense.income;
                window.incomeExpenseChart.data.datasets[1].data = chartData.income_expense.expenses;
                window.incomeExpenseChart.update();
            }

            if (chartData.expenses_distribution && window.expensesDistributionChart) {
                window.expensesDistributionChart.data.labels = chartData.expenses_distribution.labels;
                window.expensesDistributionChart.data.datasets[0].data = chartData.expenses_distribution.values;
                window.expensesDistributionChart.update();
            }

            if (chartData.monthly_trend && window.monthlyTrendChart) {
                window.monthlyTrendChart.data.labels = chartData.monthly_trend.labels;
                window.monthlyTrendChart.data.datasets[0].data = chartData.monthly_trend.values;
                window.monthlyTrendChart.update();
            }
        }

        // Export Financial Report
        async function exportFinancialReport(format) {
            const period = document.getElementById('timePeriod').value;
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;

            try {
                const response = await fetch(`../../api/reports.php?action=export_financial&format=${format}&period=${period}&from=${dateFrom}&to=${dateTo}`);

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `financial_report_${new Date().toISOString().split('T')[0]}.${format}`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);

                    showToast('تم تصدير التقرير المالي بنجاح', 'success');
                }
            } catch (error) {
                console.error('Error exporting financial report:', error);
                showToast('حدث خطأ أثناء تصدير التقرير', 'error');
            }
        }

        // View Transaction Details
        function viewTransaction(transactionId) {
            // Implement transaction details view
            showToast('سيتم عرض تفاصيل المعاملة', 'info');
        }

        // Refresh Transactions
        function refreshTransactions() {
            loadFinancialData();
            showToast('تم تحديث البيانات', 'success');
        }

        // Helper: Format Currency
        function formatCurrency(amount) {
            return parseFloat(amount).toLocaleString('ar-EG') + ' ₪';
        }

        // Helper: Show Toast
        function showToast(message, type = 'info') {
            // Using app.js toast function if available
            if (typeof toast !== 'undefined') {
                toast(message, type);
            } else {
                alert(message);
            }
        }
    </script>
</body>

</html>