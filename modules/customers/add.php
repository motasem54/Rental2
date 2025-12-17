<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$pageTitle = 'إضافة عميل جديد';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-plus me-2"></i>إضافة عميل جديد</h2>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right me-2"></i>العودة
            </a>
        </div>

        <div class="glass-card">
            <form id="addCustomerForm" class="ajax-form">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">الاسم الكامل *</label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">البريد الإلكتروني *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">رقم الهاتف *</label>
                        <input type="tel" class="form-control" name="phone" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">نوع العميل</label>
                        <select class="form-select" name="customer_type">
                            <option value="individual">فرد</option>
                            <option value="company">شركة</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">العنوان</label>
                        <textarea class="form-control" name="address" rows="3"></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-orange">
                    <i class="fas fa-save me-2"></i>حفظ العميل
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Wait for document to be ready and ensure jQuery is loaded
    document.addEventListener('DOMContentLoaded', function () {
        $(document).ready(function () {
            $('#addCustomerForm').on('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'add_customer');

                fetch('../../api/customers.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Use Toastify if available, otherwise alert
                            if (typeof Toastify === 'function') {
                                Toastify({
                                    text: "تم إضافة العميل بنجاح",
                                    duration: 3000,
                                    close: true,
                                    gravity: "top",
                                    position: "center",
                                    backgroundColor: "#4fbe87",
                                }).showToast();
                            } else {
                                alert('تم إضافة العميل بنجاح');
                            }
                            setTimeout(() => {
                                window.location.href = 'index.php';
                            }, 1500);
                        } else {
                            if (typeof Toastify === 'function') {
                                Toastify({
                                    text: 'حدث خطأ: ' + (data.message || 'Unknown error'),
                                    duration: 3000,
                                    close: true,
                                    gravity: "top",
                                    position: "center",
                                    backgroundColor: "#dc3545",
                                }).showToast();
                            } else {
                                alert('حدث خطأ: ' + data.message);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('حدث خطأ في الاتصال');
                    });
            });
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>