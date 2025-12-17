<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$pageTitle = 'جدول الصيانة';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-calendar-alt me-2"></i>جدول الصيانة</h2>
            <button class="btn btn-orange" onclick="scheduleMaintenance()">
                <i class="fas fa-plus me-2"></i>جدولة صيانة
            </button>
        </div>
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="glass-card text-center p-4">
                    <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                    <h3 id="pendingCount">0</h3>
                    <p>صيانة معلقة</p>
                </div>
            </div>
            <div class="col-lg-3 mb-4">
                <div class="glass-card text-center p-4">
                    <i class="fas fa-spinner fa-3x text-primary mb-3"></i>
                    <h3 id="inProgressCount">0</h3>
                    <p>قيد التنفيذ</p>
                </div>
            </div>
            <div class="col-lg-3 mb-4">
                <div class="glass-card text-center p-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h3 id="completedCount">0</h3>
                    <p>مكتملة</p>
                </div>
            </div>
            <div class="col-lg-3 mb-4">
                <div class="glass-card text-center p-4">
                    <i class="fas fa-dollar-sign fa-3x text-info mb-3"></i>
                    <h3 id="totalCost">0 ₪</h3>
                    <p>التكلفة الكلية</p>
                </div>
            </div>
        </div>
        <div class="glass-card">
            <h5 class="mb-3">الصيانة القادمة</h5>
            <div id="upcomingMaintenance">
                <p class="text-muted">جاري التحميل...</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        fetch('../../api/maintenance.php?action=schedule')
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    document.getElementById('pendingCount').textContent = d.data.pending || 0;
                    document.getElementById('inProgressCount').textContent = d.data.in_progress || 0;
                    document.getElementById('completedCount').textContent = d.data.completed || 0;
                    document.getElementById('totalCost').textContent = (d.data.total_cost || 0) + ' ₪';
                }
            });
    });
    function scheduleMaintenance() { window.location.href = 'add.php'; }
</script>

<?php include '../../includes/footer.php'; ?>