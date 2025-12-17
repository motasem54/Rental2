<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$pageTitle = 'إدارة التأمين';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-shield-alt me-2"></i>إدارة التأمين</h2>
            <button class="btn btn-orange" onclick="addInsurance()">
                <i class="fas fa-plus me-2"></i>إضافة وثيقة تأمين
            </button>
        </div>
        <div class="glass-card">
            <div class="table-responsive">
                <table class="table table-hover datatable" id="insuranceTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>السيارة</th>
                            <th>شركة التأمين</th>
                            <th>رقم الوثيقة</th>
                            <th>تاريخ البدء</th>
                            <th>تاريخ الانتهاء</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        $(document).ready(function () {
            $('#insuranceTable').DataTable({
                ajax: { url: '../../api/insurance.php?action=list', dataSrc: 'data' },
                columns: [
                    { data: 'id' },
                    { data: 'car_name' },
                    { data: 'company' },
                    { data: 'policy_number' },
                    { data: 'start_date' },
                    { data: 'end_date' },
                    { data: 'status' },
                    {
                        data: null, render: function (d) {
                            return `
                                <button class="btn btn-sm btn-outline-primary" onclick="viewInsurance(${d.id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteInsurance(${d.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            `;
                        }
                    }
                ],
                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json' }
            });
        });
    });

    function addInsurance() { window.location.href = 'add.php'; }
    function viewInsurance(id) { window.location.href = `claims.php?id=${id}`; }

    function deleteInsurance(id) {
        if (confirm('هل أنت متأكد من حذف وثيقة التأمين هذه؟')) {
            fetch('../../api/insurance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete&id=${id}`
            })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        $('#insuranceTable').DataTable().ajax.reload();
                        Toastify({ text: "تم حذف الوثيقة بنجاح", duration: 3000, backgroundColor: "#4fbe87" }).showToast();
                    } else {
                        Toastify({ text: d.message || "حدث خطأ", duration: 3000, backgroundColor: "#f27474" }).showToast();
                    }
                });
        }
    }
</script>

<?php include '../../includes/footer.php'; ?>