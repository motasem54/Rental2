<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$customer_id = $_GET['id'] ?? 0;
$pageTitle = 'تفاصيل العميل';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user me-2"></i>تفاصيل العميل</h2>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right me-2"></i>العودة
            </a>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="glass-card text-center p-4">
                    <div class="avatar-lg mx-auto mb-3">
                        <i class="fas fa-user fa-4x text-primary"></i>
                    </div>
                    <h4 id="customerName">--</h4>
                    <p class="text-muted" id="customerEmail">--</p>
                    <span class="badge bg-success" id="customerStatus">نشط</span>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="glass-card p-4">
                    <h5 class="mb-3">المعلومات الأساسية</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>رقم الهاتف:</strong>
                            <p id="customerPhone">--</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>النوع:</strong>
                            <p id="customerType">--</p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <strong>العنوان:</strong>
                            <p id="customerAddress">--</p>
                        </div>
                    </div>
                </div>

                <div class="glass-card p-4 mt-4">
                    <h5 class="mb-3">سجل الحجوزات</h5>
                    <div id="customerBookings">
                        <p class="text-muted">جاري التحميل...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const customerId = <?php echo $customer_id; ?>;

        fetch(`../../api/customers.php?action=get_customer&id=${customerId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const customer = data.customer;
                    document.getElementById('customerName').textContent = customer.full_name;
                    document.getElementById('customerEmail').textContent = customer.email;
                    document.getElementById('customerPhone').textContent = customer.phone;
                    document.getElementById('customerType').textContent = customer.type;
                    document.getElementById('customerAddress').textContent = customer.address || 'غير محدد';
                }
            });
    });
</script>

<?php include '../../includes/footer.php'; ?>