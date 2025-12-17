<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$pageTitle = 'مطالبات التأمين';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-file-medical me-2"></i>مطالبات التأمين</h2>
            <button class="btn btn-orange" onclick="addClaim()">
                <i class="fas fa-plus me-2"></i>إضافة مطالبة
            </button>
        </div>
        <div class="glass-card">
            <div class="table-responsive">
                <table class="table table-hover datatable" id="claimsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>رقم الوثيقة</th>
                            <th>السيارة</th>
                            <th>نوع المطالبة</th>
                            <th>التاريخ</th>
                            <th>المبلغ</th>
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
            $('#claimsTable').DataTable({
                ajax: { url: '../../api/insurance.php?action=claims', dataSrc: 'data' },
                columns: [
                    { data: 'id' },
                    { data: 'policy_number' },
                    { data: 'car_name' },
                    { data: 'type' },
                    { data: 'date' },
                    { data: 'amount' },
                    { data: 'status' },
                    {
                        data: null, render: function (d) {
                            return `<button class="btn btn-sm btn-outline-primary" onclick="viewClaim(${d.id})">
                        <i class="fas fa-eye"></i></button>`;
                        }
                    }
                ],
                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json' }
            });
        });
    });
    function addClaim() { alert('سيتم إضافة مطالبة جديدة'); }
    function viewClaim(id) { alert('عرض مطالبة رقم: ' + id); }
</script>

<?php include '../../includes/footer.php'; ?>