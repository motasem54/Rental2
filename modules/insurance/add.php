<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$pageTitle = "إضافة وثيقة تأمين - نظام تأجير السيارات";
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
                    <h2><i class="fas fa-shield-alt me-2"></i> إضافة وثيقة تأمين جديدة</h2>
                    <small class="text-muted">قم بتعبئة بيانات وثيقة التأمين</small>
                </div>
                <button class="btn btn-secondary" onclick="window.location.href='index.php'">
                    <i class="fas fa-arrow-right me-2"></i> العودة
                </button>
            </div>

            <form id="insuranceForm" class="ajax-form" data-action="../../api/insurance.php?action=create">
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
                            <h5 class="mb-3"><i class="fas fa-building me-2"></i> بيانات شركة التأمين</h5>

                            <div class="mb-3">
                                <label class="form-label">شركة التأمين <span class="text-danger">*</span></label>
                                <input type="text" class="form-control glass-input" id="insurance_company"
                                    name="insurance_company" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">رقم الوثيقة <span class="text-danger">*</span></label>
                                <input type="text" class="form-control glass-input" id="policy_number"
                                    name="policy_number" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">نوع التغطية <span class="text-danger">*</span></label>
                                <select class="form-select glass-input" id="coverage_type" name="coverage_type"
                                    required>
                                    <option value="">اختر نوع التغطية...</option>
                                    <option value="comprehensive">شامل</option>
                                    <option value="third_party">طرف ثالث</option>
                                    <option value="collision">تصادم</option>
                                    <option value="theft">سرقة</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="glass-card">
                            <h5 class="mb-3"><i class="fas fa-calendar-alt me-2"></i> فترة التأمين</h5>

                            <div class="mb-3">
                                <label class="form-label">تاريخ البداية <span class="text-danger">*</span></label>
                                <input type="date" class="form-control glass-input" id="start_date" name="start_date"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">تاريخ الانتهاء <span class="text-danger">*</span></label>
                                <input type="date" class="form-control glass-input" id="end_date" name="end_date"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">المدة (أيام)</label>
                                <input type="number" class="form-control glass-input" id="duration" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="glass-card">
                            <h5 class="mb-3"><i class="fas fa-money-bill-wave me-2"></i> التفاصيل المالية</h5>

                            <div class="mb-3">
                                <label class="form-label">قيمة القسط (₪) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control glass-input" id="premium_amount"
                                    name="premium_amount" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">المبلغ القابل للخصم (₪)</label>
                                <input type="number" step="0.01" class="form-control glass-input" id="deductible"
                                    name="deductible">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">حد التغطية (₪)</label>
                                <input type="number" step="0.01" class="form-control glass-input" id="coverage_limit"
                                    name="coverage_limit">
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12 mb-4">
                        <div class="glass-card">
                            <h5 class="mb-3"><i class="fas fa-user-tie me-2"></i> بيانات الوكيل</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">اسم الوكيل</label>
                                    <input type="text" class="form-control glass-input" id="agent_name"
                                        name="agent_name">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">هاتف الوكيل</label>
                                    <input type="text" class="form-control glass-input" id="agent_phone"
                                        name="agent_phone">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ملاحظات</label>
                                <textarea class="form-control glass-input" id="notes" name="notes" rows="3"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">حالة الوثيقة</label>
                                <select class="form-select glass-input" id="status" name="status">
                                    <option value="active">نشطة</option>
                                    <option value="expired">منتهية</option>
                                    <option value="cancelled">ملغية</option>
                                </select>
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
                                    <i class="fas fa-save me-2"></i> حفظ وثيقة التأمين
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>