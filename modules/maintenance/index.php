<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$pageTitle = 'إدارة الصيانة';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tools me-2"></i>إدارة الصيانة</h2>
            <button class="btn btn-orange" onclick="addMaintenance()">
                <i class="fas fa-plus me-2"></i>إضافة سجل صيانة
            </button>
        </div>
        <div class="glass-card">
            <div class="table-responsive">
                <table class="table table-hover datatable" id="maintenanceTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>السيارة</th>
                            <th>نوع الصيانة</th>
                            <th>التاريخ</th>
                            <th>التكلفة</th>
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
            $('#maintenanceTable').DataTable({
                ajax: { url: '../../api/maintenance.php?action=list', dataSrc: 'data' },
                columns: [
                    { data: 'id' },
                    { data: 'car_name' },
                    { data: 'type' },
                    { data: 'date' },
                    { data: 'cost' },
                    { data: 'status' },
                    {
                        data: null, render: function (d) {
                            return `
                                <button class="btn btn-sm btn-outline-primary" onclick="viewMaintenance(${d.id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteMaintenance(${d.id})">
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

    function addMaintenance() { window.location.href = 'add.php'; }
    function viewMaintenance(id) { window.location.href = `schedule.php?id=${id}`; }

    function deleteMaintenance(id) {
        if (confirm('هل أنت متأكد من حذف سجل الصيانة هذا؟')) {
            fetch('../../api/maintenance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete&id=${id}`
            })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        $('#maintenanceTable').DataTable().ajax.reload();
                        Toastify({ text: "تم حذف السجل بنجاح", duration: 3000, backgroundColor: "#4fbe87" }).showToast();
                    } else {
                        Toastify({ text: d.message || "حدث خطأ", duration: 3000, backgroundColor: "#f27474" }).showToast();
                    }
                });
        }
    }
</script>

<?php include '../../includes/footer.php'; ?>