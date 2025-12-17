<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$pageTitle = "إضافة سجل صيانة - نظام تأجير السيارات";
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>

<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-tools me-2"></i> إضافة سجل صيانة جديد</h2>
                    <small class="text-muted">قم بتعبئة بيانات عملية الصيانة</small>
                </div>
                <button class="btn btn-secondary" onclick="window.location.href='index.php'">
                    <i class="fas fa-arrow-right me-2"></i> العودة
                </button>
            </div>

            <form id="maintenanceForm" class="ajax-form" data-action="../../api/maintenance.php?action=create">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="glass-card">
                            <h5 class="mb-3"><i class="fas fa-car me-2"></i> بيانات السيارة</h5>

                            <div class="mb-3">
                                <label class="form-label">السيارة <span class="text-danger">*</span></label>
                                <select class="form-select glass-input" id="car_id" name="car_id" required>
                                    <option value="">اختر السيارة...</option>
                                </select>
                            </div>

                            <div id="carInfo" class="alert alert-info" style="display: none;">
                                <h6>معلومات السيارة:</h6>
                                <div id="carInfoContent"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="glass-card">
                            <h5 class="mb-3"><i class="fas fa-wrench me-2"></i> نوع الصيانة</h5>

                            <div class="mb-3">
                                <label class="form-label">نوع الصيانة <span class="text-danger">*</span></label>
                                <select class="form-select glass-input" id="maintenance_type" name="maintenance_type"
                                    required>
                                    <option value="">اختر نوع الصيانة...</option>
                                    <option value="routine">صيانة دورية</option>
                                    <option value="repair">إصلاح</option>
                                    <option value="accident">حادث</option>
                                    <option value="recall">استدعاء</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">الوصف <span class="text-danger">*</span></label>
                                <textarea class="form-control glass-input" id="description" name="description" rows="3"
                                    required placeholder="صف نوع الصيانة المطلوبة..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="glass-card">
                            <h5 class="mb-3"><i class="fas fa-calendar-alt me-2"></i> التواريخ</h5>

                            <div class="mb-3">
                                <label class="form-label">تاريخ البدء <span class="text-danger">*</span></label>
                                <input type="date" class="form-control glass-input" id="start_date" name="start_date"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">تاريخ الانتهاء المتوقع</label>
                                <input type="date" class="form-control glass-input" id="end_date" name="end_date">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">الحالة</label>
                                <select class="form-select glass-input" id="status" name="status">
                                    <option value="scheduled">مجدولة</option>
                                    <option value="in_progress">قيد التنفيذ</option>
                                    <option value="completed">مكتملة</option>
                                    <option value="cancelled">ملغية</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="glass-card">
                            <h5 class="mb-3"><i class="fas fa-money-bill-wave me-2"></i> التكلفة</h5>

                            <div class="mb-3">
                                <label class="form-label">التكلفة (₪) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control glass-input" id="cost" name="cost"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ساعات العمل</label>
                                <input type="number" step="0.5" class="form-control glass-input" id="labor_hours"
                                    name="labor_hours">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">رقم الفاتورة</label>
                                <input type="text" class="form-control glass-input" id="invoice_number"
                                    name="invoice_number">
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12 mb-4">
                        <div class="glass-card">
                            <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i> تفاصيل إضافية</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">المورد/الورشة</label>
                                    <input type="text" class="form-control glass-input" id="vendor" name="vendor">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الفني المسؤول</label>
                                    <input type="text" class="form-control glass-input" id="technician"
                                        name="technician">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">القطع المستبدلة</label>
                                <textarea class="form-control glass-input" id="parts_replaced" name="parts_replaced"
                                    rows="2" placeholder="قائمة القطع التي تم استبدالها..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ملاحظات</label>
                                <textarea class="form-control glass-input" id="notes" name="notes" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="glass-card">
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary"
                                    onclick="window.location.href='index.php'">
                                    <i class="fas fa-times me-2"></i> إلغاء
                                </button>
                                <button type="submit" class="btn btn-orange">
                                    <i class="fas fa-save me-2"></i> حفظ سجل الصيانة
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>