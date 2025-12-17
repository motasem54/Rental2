<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$db = Database::getInstance()->getConnection();
$pageTitle = 'إدارة العملاء';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-users me-2"></i>إدارة العملاء</h2>
            <a href="add.php" class="btn btn-orange">
                <i class="fas fa-plus me-2"></i>إضافة عميل جديد
            </a>
        </div>

        <div class="glass-card">
            <div class="table-responsive">
                <table class="table table-hover datatable" id="customersTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الاسم الكامل</th>
                            <th>البريد الإلكتروني</th>
                            <th>الهاتف</th>
                            <th>النوع</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="customersBody">
                        <!-- Data loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        $(document).ready(function () {
            $('#customersTable').DataTable({
                ajax: {
                    url: '../../api/customers.php?action=get_customers',
                    dataSrc: 'customers'
                },
                columns: [
                    { data: 'id' },
                    { data: 'full_name' },
                    { data: 'email' },
                    { data: 'phone' },
                    { data: 'type' },
                    { data: 'status' },
                    {
                        data: null,
                        render: function (data) {
                            return `
                            <a href="view.php?id=${data.id}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteCustomer(${data.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        `;
                        }
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
                }
            });
        });
    });

    function deleteCustomer(id) {
        if (confirm('هل أنت متأكد من حذف هذا العميل؟')) {
            fetch(`../../api/customers.php?action=delete_customer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#customersTable').DataTable().ajax.reload();
                        Toastify({
                            text: "تم حذف العميل بنجاح",
                            duration: 3000,
                            gravity: "top",
                            position: "center",
                            backgroundColor: "#4fbe87",
                        }).showToast();
                    } else {
                        Toastify({
                            text: data.message || "حدث خطأ أثناء الحذف",
                            duration: 3000,
                            gravity: "top",
                            position: "center",
                            backgroundColor: "#f27474",
                        }).showToast();
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    }
</script>

<?php include '../../includes/footer.php'; ?>