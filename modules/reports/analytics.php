<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$db = Database::getInstance()->getConnection();
$pageTitle = 'التحليلات والإحصائيات';
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .analytics-card {
            background: rgba(255,255,255,0.05);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid var(--glass-border);
            transition: all 0.3s ease;
        }
        
        .analytics-card:hover {
            transform: translateY(-3px);
            border-color: var(--primary);
        }
        
        .metric-box {
            text-align: center;
            padding: 25px;
        }
        
        .metric-box .value {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .metric-box .label {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .trend-indicator {
            font-size: 0.85rem;
            margin-top: 8px;
        }
        
        .trend-up { color: #28a745; }
        .trend-down { color: #dc3545; }
        
        .chart-container {
            position: relative;
            height: 350px;
            margin-bottom: 20px;
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
                    <h2><i class="fas fa-chart-bar me-2"></i><?php echo $pageTitle; ?></h2>
                    <small class="text-muted">تحليلات متقدمة لأداء الأعمال</small>
                </div>
                
                <div>
                    <button class="btn btn-orange" onclick="refreshAnalytics()">
                        <i class="fas fa-sync-alt me-2"></i>تحديث
                    </button>
                </div>
            </div>
            
            <!-- Key Metrics -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="analytics-card">
                        <div class="metric-box">
                            <i class="fas fa-users fa-2x text-primary mb-3"></i>
                            <div class="value text-primary" id="totalCustomers">0</div>
                            <div class="label">إجمالي العملاء</div>
                            <div class="trend-indicator trend-up" id="customersTrend">
                                <i class="fas fa-arrow-up"></i> +15%
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="analytics-card">
                        <div class="metric-box">
                            <i class="fas fa-car fa-2x text-success mb-3"></i>
                            <div class="value text-success" id="activeRentals">0</div>
                            <div class="label">عمليات تأجير نشطة</div>
                            <div class="trend-indicator trend-up" id="rentalsTrend">
                                <i class="fas fa-arrow-up"></i> +8%
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="analytics-card">
                        <div class="metric-box">
                            <i class="fas fa-chart-line fa-2x text-warning mb-3"></i>
                            <div class="value text-warning" id="avgRevenue">0 ₪</div>
                            <div class="label">متوسط الإيراد اليومي</div>
                            <div class="trend-indicator trend-up" id="revenueTrend">
                                <i class="fas fa-arrow-up"></i> +12%
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="analytics-card">
                        <div class="metric-box">
                            <i class="fas fa-percentage fa-2x text-info mb-3"></i>
                            <div class="value text-info" id="utilizationRate">0%</div>
                            <div class="label">معدل استخدام السيارات</div>
                            <div class="trend-indicator trend-up" id="utilizationTrend">
                                <i class="fas fa-arrow-up"></i> +5%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row 1 -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="glass-card">
                        <h5 class="mb-3"><i class="fas fa-chart-area me-2"></i>تحليل الإيرادات</h5>
                        <div class="chart-container">
                            <canvas id="revenueAnalysisChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="glass-card h-100">
                        <h5 class="mb-3"><i class="fas fa-chart-pie me-2"></i>توزيع العملاء</h5>
                        <div class="chart-container">
                            <canvas id="customerDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row 2 -->
            <div class="row mb-4">
                <div class="col-lg-6">
                    <div class="glass-card">
                        <h5 class="mb-3"><i class="fas fa-chart-bar me-2"></i>أداء السيارات</h5>
                        <div class="chart-container">
                            <canvas id="carPerformanceChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="glass-card">
                        <h5 class="mb-3"><i class="fas fa-clock me-2"></i>ساعات التأجير الأكثر نشاطاً</h5>
                        <div class="chart-container">
                            <canvas id="peakHoursChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Insights Section -->
            <div class="glass-card">
                <h5 class="mb-3"><i class="fas fa-lightbulb me-2"></i>رؤى ذكية</h5>
                <div id="insightsList">
                    <!-- Insights will be loaded here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript Files -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../assets/js/app.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        loadAnalyticsData();
        initCharts();
    });
    
    async function loadAnalyticsData() {
        try {
            const response = await fetch('../../api/reports.php?action=analytics_data');
            const data = await response.json();
            
            if (data.success) {
                updateMetrics(data.metrics);
                updateCharts(data.charts);
                displayInsights(data.insights);
            }
        } catch (error) {
            console.error('Error loading analytics:', error);
        }
    }
    
    function updateMetrics(metrics) {
        document.getElementById('totalCustomers').textContent = metrics.total_customers || 0;
        document.getElementById('activeRentals').textContent = metrics.active_rentals || 0;
        document.getElementById('avgRevenue').textContent = (metrics.avg_revenue || 0) + ' ₪';
        document.getElementById('utilizationRate').textContent = (metrics.utilization_rate || 0) + '%';
    }
    
    function initCharts() {
        // Revenue Analysis Chart
        const ctx1 = document.getElementById('revenueAnalysisChart').getContext('2d');
        window.revenueAnalysisChart = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                datasets: [{
                    label: 'الإيرادات',
                    data: [12000, 19000, 15000, 25000, 22000, 30000],
                    borderColor: '#FF5722',
                    backgroundColor: 'rgba(255, 87, 34, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: 'white' } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: 'white' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    },
                    x: {
                        ticks: { color: 'white' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    }
                }
            }
        });
        
        // Customer Distribution Chart
        const ctx2 = document.getElementById('customerDistributionChart').getContext('2d');
        window.customerDistributionChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['أفراد', 'شركات', 'دائمين'],
                datasets: [{
                    data: [60, 25, 15],
                    backgroundColor: ['#FF5722', '#28a745', '#17a2b8']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: 'white' }
                    }
                }
            }
        });
        
        // Car Performance Chart
        const ctx3 = document.getElementById('carPerformanceChart').getContext('2d');
        window.carPerformanceChart = new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: ['SUV', 'سيدان', 'رياضية', 'اقتصادية', 'فان'],
                datasets: [{
                    label: 'عدد التأجيرات',
                    data: [45, 60, 25, 80, 30],
                    backgroundColor: 'rgba(255, 87, 34, 0.7)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: 'white' } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: 'white' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    },
                    x: {
                        ticks: { color: 'white' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    }
                }
            }
        });
        
        // Peak Hours Chart
        const ctx4 = document.getElementById('peakHoursChart').getContext('2d');
        window.peakHoursChart = new Chart(ctx4, {
            type: 'line',
            data: {
                labels: ['8 ص', '10 ص', '12 م', '2 م', '4 م', '6 م', '8 م'],
                datasets: [{
                    label: 'عدد العمليات',
                    data: [5, 12, 18, 25, 22, 15, 8],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: 'white' } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: 'white' },
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
    
    function updateCharts(chartData) {
        // Update charts with real data when available
    }
    
    function displayInsights(insights) {
        const container = document.getElementById('insightsList');
        let html = '<div class="row">';
        
        const defaultInsights = [
            { icon: 'chart-line', color: 'success', text: 'الإيرادات في ازدياد مستمر بنسبة 12% هذا الشهر' },
            { icon: 'users', color: 'primary', text: 'عدد العملاء الجدد زاد بنسبة 15% مقارنة بالشهر الماضي' },
            { icon: 'car', color: 'warning', text: 'السيارات الاقتصادية هي الأكثر طلباً (35%)' },
            { icon: 'clock', color: 'info', text: 'أكثر ساعات النشاط من 2 م إلى 6 م' }
        ];
        
        const displayInsights = insights || defaultInsights;
        
        displayInsights.forEach(insight => {
            html += `
            <div class="col-lg-6 mb-3">
                <div class="alert alert-${insight.color || 'info'} mb-0">
                    <i class="fas fa-${insight.icon || 'lightbulb'} me-2"></i>
                    ${insight.text}
                </div>
            </div>`;
        });
        
        html += '</div>';
        container.innerHTML = html;
    }
    
    function refreshAnalytics() {
        loadAnalyticsData();
        showToast('تم تحديث التحليلات', 'success');
    }
    
    function showToast(message, type = 'info') {
        if (typeof toast !== 'undefined') {
            toast(message, type);
        } else {
            alert(message);
        }
    }
    </script>
</body>
</html>
