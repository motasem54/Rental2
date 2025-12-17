<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../../login.php');
    exit();
}

$db = Database::getInstance()->getConnection();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعدادات النظام</title>

    <link rel="stylesheet" href="../../assets/css/glassmorphism.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <style>
        .settings-nav {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            height: 100%;
        }

        .settings-nav .nav-link {
            color: #ccc;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }

        .settings-nav .nav-link:hover,
        .settings-nav .nav-link.active {
            color: white;
            background: rgba(255, 87, 34, 0.2);
            border-right: 3px solid var(--primary);
        }

        .settings-section {
            display: none;
        }

        .settings-section.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 5px;
            display: inline-block;
            margin-right: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logo-preview {
            max-width: 200px;
            max-height: 100px;
            object-fit: contain;
            border-radius: 10px;
            border: 2px dashed rgba(255, 255, 255, 0.2);
            padding: 10px;
        }

        .backup-card {
            border-left: 5px solid;
            padding: 15px;
            margin-bottom: 15px;
        }

        .backup-success {
            border-left-color: #28a745;
        }

        .backup-failed {
            border-left-color: #dc3545;
        }

        .api-test-result {
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
            display: none;
        }

        .api-test-success {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid #28a745;
        }

        .api-test-error {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
        }
    </style>
</head>

<body>
    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-cog me-2"></i> إعدادات النظام</h2>
                    <small class="text-muted">إدارة إعدادات وتكوين النظام</small>
                </div>

                <div>
                    <button class="btn btn-orange me-2" onclick="saveAllSettings()">
                        <i class="fas fa-save me-2"></i> حفظ جميع التغييرات
                    </button>

                    <button class="btn btn-outline-light" onclick="createSystemBackup()">
                        <i class="fas fa-database me-2"></i> نسخ احتياطي
                    </button>
                </div>
            </div>

            <div class="row">
                <!-- Settings Navigation -->
                <div class="col-lg-3 col-md-4 mb-4">
                    <div class="settings-nav glass-card">
                        <h5 class="mb-3"><i class="fas fa-sliders-h me-2"></i> الأقسام</h5>

                        <nav class="nav flex-column">
                            <a href="#" class="nav-link active" onclick="showSection('general')">
                                <i class="fas fa-globe me-2"></i> الإعدادات العامة
                            </a>

                            <a href="#" class="nav-link" onclick="showSection('appearance')">
                                <i class="fas fa-paint-brush me-2"></i> المظهر والتصميم
                            </a>

                            <a href="#" class="nav-link" onclick="showSection('financial')">
                                <i class="fas fa-money-bill-wave me-2"></i> الإعدادات المالية
                            </a>

                            <a href="#" class="nav-link" onclick="showSection('whatsapp')">
                                <i class="fab fa-whatsapp me-2"></i> إعدادات واتساب
                            </a>

                            <a href="#" class="nav-link" onclick="showSection('email')">
                                <i class="fas fa-envelope me-2"></i> إعدادات البريد
                            </a>

                            <a href="#" class="nav-link" onclick="showSection('security')">
                                <i class="fas fa-shield-alt me-2"></i> الأمان والحماية
                            </a>

                            <a href="#" class="nav-link" onclick="showSection('backup')">
                                <i class="fas fa-database me-2"></i> النسخ الاحتياطي
                            </a>

                            <a href="#" class="nav-link" onclick="showSection('api')">
                                <i class="fas fa-code me-2"></i> API والمطورين
                            </a>

                            <a href="#" class="nav-link" onclick="showSection('system')">
                                <i class="fas fa-server me-2"></i> معلومات النظام
                            </a>
                        </nav>

                        <!-- System Status -->
                        <div class="mt-4 pt-3 border-top">
                            <h6><i class="fas fa-info-circle me-2"></i> حالة النظام</h6>
                            <div class="system-status">
                                <small class="d-block mb-1">
                                    <i class="fas fa-database text-info me-2"></i>
                                    قاعدة البيانات: <span id="dbStatus">✅ نشطة</span>
                                </small>
                                <small class="d-block mb-1">
                                    <i class="fas fa-hdd text-success me-2"></i>
                                    المساحة: <span id="diskSpace">0 GB حر</span>
                                </small>
                                <small class="d-block">
                                    <i class="fas fa-memory text-warning me-2"></i>
                                    الذاكرة: <span id="memoryUsage">0%</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Content -->
                <div class="col-lg-9 col-md-8">
                    <!-- General Settings -->
                    <div class="settings-section active" id="general-settings">
                        <div class="glass-card">
                            <h4 class="mb-3"><i class="fas fa-globe me-2"></i> الإعدادات العامة</h4>

                            <form id="generalSettingsForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">اسم النظام</label>
                                            <input type="text" class="form-control glass-input" name="system_name"
                                                value="نظام تأجير السيارات المتقدم">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">اسم الشركة</label>
                                            <input type="text" class="form-control glass-input" name="company_name"
                                                value="شركة تأجير السيارات">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">العنوان</label>
                                            <input type="text" class="form-control glass-input" name="company_address"
                                                value="جدة، السعودية">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">رقم الهاتف</label>
                                            <input type="text" class="form-control glass-input" name="company_phone"
                                                value="+966500000000">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">البريد الإلكتروني</label>
                                            <input type="email" class="form-control glass-input" name="company_email"
                                                value="info@carrental.com">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">الموقع الإلكتروني</label>
                                            <input type="url" class="form-control glass-input" name="company_website"
                                                value="https://carrental.com">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">اللغة الافتراضية</label>
                                    <select class="form-select glass-input" name="default_language">
                                        <option value="ar" selected>العربية</option>
                                        <option value="en">الإنجليزية</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">المنطقة الزمنية</label>
                                    <select class="form-select glass-input" name="timezone">
                                        <option value="Asia/Riyadh" selected>السعودية (الرياض)</option>
                                        <option value="Asia/Dubai">الإمارات (دبي)</option>
                                        <option value="Asia/Qatar">قطر (الدوحة)</option>
                                        <option value="Asia/Kuwait">الكويت</option>
                                    </select>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="maintenance_mode"
                                        id="maintenanceMode">
                                    <label class="form-check-label" for="maintenanceMode">
                                        وضع الصيانة
                                    </label>
                                    <small class="text-muted d-block">عند التفعيل، سيكون النظام غير متاح للعملاء</small>
                                </div>

                                <button type="submit" class="btn btn-orange">
                                    <i class="fas fa-save me-2"></i> حفظ الإعدادات العامة
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Appearance Settings -->
                    <div class="settings-section" id="appearance-settings">
                        <div class="glass-card">
                            <h4 class="mb-3"><i class="fas fa-paint-brush me-2"></i> المظهر والتصميم</h4>

                            <form id="appearanceSettingsForm">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h5>الألوان</h5>

                                        <div class="mb-3">
                                            <label class="form-label">اللون الرئيسي</label>
                                            <div class="input-group">
                                                <span class="color-preview" id="primaryColorPreview"
                                                    style="background: #FF5722;"></span>
                                                <input type="color" class="form-control glass-input" id="primaryColor"
                                                    name="primary_color" value="#FF5722">
                                                <input type="text" class="form-control glass-input" id="primaryColorHex"
                                                    value="#FF5722" readonly>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">اللون الثانوي</label>
                                            <div class="input-group">
                                                <span class="color-preview" id="secondaryColorPreview"
                                                    style="background: #121212;"></span>
                                                <input type="color" class="form-control glass-input" id="secondaryColor"
                                                    name="secondary_color" value="#121212">
                                                <input type="text" class="form-control glass-input"
                                                    id="secondaryColorHex" value="#121212" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <h5>الشعار</h5>

                                        <div class="mb-3">
                                            <label class="form-label">الشعار الحالي</label>
                                            <div>
                                                <img src="../../assets/images/logo.png" alt="الشعار"
                                                    class="logo-preview mb-2" id="currentLogo">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">تحميل شعار جديد</label>
                                            <input type="file" class="form-control glass-input" id="logoUpload"
                                                name="logo" accept="image/*">
                                            <small class="text-muted">الصيغ المدعومة: PNG, JPG, SVG | الحد الأقصى:
                                                2MB</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">أيقونة الموقع (Favicon)</label>
                                            <input type="file" class="form-control glass-input" name="favicon"
                                                accept="image/*">
                                            <small class="text-muted">الحجم الموصى به: 32×32 أو 64×64 بكسل</small>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="mb-3">خيارات الواجهة</h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" name="dark_mode"
                                                id="darkMode" checked>
                                            <label class="form-check-label" for="darkMode">
                                                الوضع الداكن
                                            </label>
                                        </div>

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" name="rtl_mode" id="rtlMode"
                                                checked>
                                            <label class="form-check-label" for="rtlMode">
                                                اتجاه من اليمين لليسار (RTL)
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" name="glass_effect"
                                                id="glassEffect" checked>
                                            <label class="form-check-label" for="glassEffect">
                                                تأثير الزجاج (Glassmorphism)
                                            </label>
                                        </div>

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" name="animations"
                                                id="animations" checked>
                                            <label class="form-check-label" for="animations">
                                                الحركات والانيميشن
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-orange">
                                    <i class="fas fa-save me-2"></i> حفظ إعدادات المظهر
                                </button>

                                <button type="button" class="btn btn-outline-light ms-2" onclick="resetAppearance()">
                                    <i class="fas fa-redo me-2"></i> إعادة تعيين
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Financial Settings -->
                    <div class="settings-section" id="financial-settings">
                        <div class="glass-card">
                            <h4 class="mb-3"><i class="fas fa-money-bill-wave me-2"></i> الإعدادات المالية</h4>

                            <form id="financialSettingsForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">العملة الافتراضية</label>
                                            <select class="form-select glass-input" name="currency">
                                                <option value="ILS" selected>شيكل إسرائيلي (₪)</option>
                                                <option value="SAR">ريال سعودي (ر.س)</option>
                                                <option value="USD">دولار أمريكي ($)</option>
                                                <option value="EUR">يورو (€)</option>
                                                <option value="AED">درهم إماراتي (د.إ)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">معدل الضريبة (%)</label>
                                            <input type="number" step="0.01" class="form-control glass-input"
                                                name="tax_rate" value="17.00">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">نسبة الدفعة المقدمة (%)</label>
                                            <input type="number" step="0.01" class="form-control glass-input"
                                                name="deposit_percentage" value="40.00">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">رسوم التأخير اليومية (₪)</label>
                                            <input type="number" step="0.01" class="form-control glass-input"
                                                name="late_fee_per_day" value="50.00">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">تكلفة الصيانة اليومية (₪)</label>
                                            <input type="number" step="0.01" class="form-control glass-input"
                                                name="maintenance_cost_per_day" value="15.00">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">أقصى فترة إيجار (أيام)</label>
                                            <input type="number" class="form-control glass-input" name="max_rental_days"
                                                value="30">
                                        </div>
                                    </div>
                                </div>

                                <h5 class="mb-3">أسعار التأمين</h5>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">تأمين كامل (%)</label>
                                            <input type="number" step="0.01" class="form-control glass-input"
                                                name="full_insurance_rate" value="15.00">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">تأمين جزئي (%)</label>
                                            <input type="number" step="0.01" class="form-control glass-input"
                                                name="partial_insurance_rate" value="12.00">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">تأمين ضد الغير (%)</label>
                                            <input type="number" step="0.01" class="form-control glass-input"
                                                name="third_party_insurance_rate" value="8.00">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="auto_generate_invoices"
                                        id="autoGenerateInvoices" checked>
                                    <label class="form-check-label" for="autoGenerateInvoices">
                                        إنشاء الفواتير تلقائياً
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-orange">
                                    <i class="fas fa-save me-2"></i> حفظ الإعدادات المالية
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- WhatsApp Settings -->
                    <div class="settings-section" id="whatsapp-settings">
                        <div class="glass-card">
                            <h4 class="mb-3"><i class="fab fa-whatsapp me-2"></i> إعدادات واتساب</h4>

                            <form id="whatsappSettingsForm">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="whatsapp_enabled"
                                        id="whatsappEnabled" checked>
                                    <label class="form-check-label" for="whatsappEnabled">
                                        تفعيل خدمة واتساب
                                    </label>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">توكن الوصول (Access Token)</label>
                                            <input type="password" class="form-control glass-input"
                                                name="whatsapp_token" placeholder="أدخل توكن واتساب">
                                            <small class="text-muted">يمكن الحصول عليه من Meta for Developers</small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">رقم الهاتف (Phone Number ID)</label>
                                            <input type="text" class="form-control glass-input" name="whatsapp_phone_id"
                                                placeholder="أدخل رقم الهاتف">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">رابط Webhook</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control glass-input" id="webhookUrl"
                                            value="https://yourdomain.com/api/whatsapp-webhook.php" readonly>
                                        <button type="button" class="btn btn-outline-light" onclick="copyWebhookUrl()">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">انسخ هذا الرابط وأضفه في إعدادات واتساب</small>
                                </div>

                                <h5 class="mb-3">الإشعارات التلقائية</h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox"
                                                name="whatsapp_booking_confirmation" id="whatsappBookingConfirmation"
                                                checked>
                                            <label class="form-check-label" for="whatsappBookingConfirmation">
                                                تأكيد الحجز
                                            </label>
                                        </div>

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox"
                                                name="whatsapp_payment_reminder" id="whatsappPaymentReminder" checked>
                                            <label class="form-check-label" for="whatsappPaymentReminder">
                                                تذكير بالدفع
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox"
                                                name="whatsapp_maintenance_alert" id="whatsappMaintenanceAlert" checked>
                                            <label class="form-check-label" for="whatsappMaintenanceAlert">
                                                تنبيهات الصيانة
                                            </label>
                                        </div>

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" name="whatsapp_promotional"
                                                id="whatsappPromotional">
                                            <label class="form-check-label" for="whatsappPromotional">
                                                رسائل ترويجية
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-orange">
                                        <i class="fas fa-save me-2"></i> حفظ إعدادات واتساب
                                    </button>

                                    <button type="button" class="btn btn-success" onclick="testWhatsAppConnection()">
                                        <i class="fab fa-whatsapp me-2"></i> اختبار الاتصال
                                    </button>

                                    <button type="button" class="btn btn-outline-info" onclick="openTemplatesModal()">
                                        <i class="fas fa-file-alt me-2"></i> إدارة القوالب
                                    </button>
                                </div>

                                <div id="whatsappTestResult" class="api-test-result"></div>
                            </form>
                        </div>
                    </div>

                    <!-- Other sections will be similar... -->

                    <!-- Email Settings -->
                    <div class="settings-section" id="email-settings">
                        <div class="glass-card">
                            <h4 class="mb-3"><i class="fas fa-envelope me-2"></i> إعدادات البريد الإلكتروني</h4>
                            <form id="emailSettingsForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">خادم SMTP</label>
                                        <input type="text" class="form-control glass-input" name="smtp_host"
                                            placeholder="smtp.gmail.com">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">منفذ SMTP</label>
                                        <input type="number" class="form-control glass-input" name="smtp_port"
                                            value="587">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">اسم المستخدم</label>
                                        <input type="text" class="form-control glass-input" name="smtp_username">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">كلمة المرور</label>
                                        <input type="password" class="form-control glass-input" name="smtp_password">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">البريد الافتراضي للإرسال</label>
                                        <input type="email" class="form-control glass-input" name="from_email">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-orange">
                                    <i class="fas fa-save me-2"></i> حفظ إعدادات البريد
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Security Settings -->
                    <div class="settings-section" id="security-settings">
                        <div class="glass-card">
                            <h4 class="mb-3"><i class="fas fa-shield-alt me-2"></i> الأمان والحماية</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="twoFactorAuth">
                                        <label class="form-check-label" for="twoFactorAuth">
                                            تفعيل المصادقة الثنائية
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="loginNotifications">
                                        <label class="form-check-label" for="loginNotifications">
                                            إشعارات تسجيل الدخول
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">مدة الجلسة (دقائق)</label>
                                    <input type="number" class="form-control glass-input" value="60">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Backup Settings -->
                    <div class="settings-section" id="backup-settings">
                        <div class="glass-card">
                            <h4 class="mb-3"><i class="fas fa-database me-2"></i> النسخ الاحتياطي</h4>
                            <div class="mb-3">
                                <button type="button" class="btn btn-orange" onclick="createBackup()">
                                    <i class="fas fa-download me-2"></i> إنشاء نسخة احتياطية الآن
                                </button>
                            </div>
                            <div id="backupsList">
                                <p class="text-muted">لا توجد نسخ احتياطية</p>
                            </div>
                        </div>
                    </div>

                    <!-- API Settings -->
                    <div class="settings-section" id="api-settings">
                        <div class="glass-card">
                            <h4 class="mb-3"><i class="fas fa-code me-2"></i> API والمطورين</h4>
                            <div class="alert alert-info">
                                <h6>معلومات API</h6>
                                <p class="mb-1"><strong>Base URL:</strong> <code>/api/</code></p>
                                <p class="mb-0"><strong>التوثيق:</strong> متوفر في <code>/docs/api.md</code></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">مفتاح API</label>
                                <div class="input-group">
                                    <input type="text" class="form-control glass-input" value="sk_test_xxxxxxxxxxxxx"
                                        readonly>
                                    <button class="btn btn-outline-light" onclick="generateApiKey()">
                                        <i class="fas fa-sync"></i> توليد جديد
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Info -->
                    <div class="settings-section" id="system-settings">
                        <div class="glass-card">
                            <h4 class="mb-3"><i class="fas fa-server me-2"></i> معلومات النظام</h4>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h5>معلومات الخادم</h5>
                                        <table class="table table-sm table-dark">
                                            <tr>
                                                <td>نظام التشغيل:</td>
                                                <td id="osInfo">...</td>
                                            </tr>
                                            <tr>
                                                <td>إصدار PHP:</td>
                                                <td id="phpVersion"><?php echo phpversion(); ?></td>
                                            </tr>
                                            <tr>
                                                <td>إصدار MySQL:</td>
                                                <td id="mysqlVersion">...</td>
                                            </tr>
                                            <tr>
                                                <td>مساحة القرص:</td>
                                                <td id="diskInfo">...</td>
                                            </tr>
                                            <tr>
                                                <td>الذاكرة:</td>
                                                <td id="memoryInfo">...</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h5>معلومات النظام</h5>
                                        <table class="table table-sm table-dark">
                                            <tr>
                                                <td>إصدار النظام:</td>
                                                <td>2.0.0</td>
                                            </tr>
                                            <tr>
                                                <td>تاريخ التثبيت:</td>
                                                <td id="installDate">...</td>
                                            </tr>
                                            <tr>
                                                <td>آخر تحديث:</td>
                                                <td id="lastUpdate">...</td>
                                            </tr>
                                            <tr>
                                                <td>إجمالي المستخدمين:</td>
                                                <td id="totalUsers">...</td>
                                            </tr>
                                            <tr>
                                                <td>إجمالي السيارات:</td>
                                                <td id="totalCars">...</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <h5>سجلات النظام</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-dark">
                                        <thead>
                                            <tr>
                                                <th>التاريخ</th>
                                                <th>النوع</th>
                                                <th>الرسالة</th>
                                            </tr>
                                        </thead>
                                        <tbody id="systemLogs">
                                            <!-- Logs will be loaded via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button class="btn btn-outline-light me-2" onclick="clearSystemLogs()">
                                    <i class="fas fa-trash me-2"></i> مسح السجلات
                                </button>

                                <button class="btn btn-outline-info" onclick="downloadSystemLogs()">
                                    <i class="fas fa-download me-2"></i> تحميل السجلات
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates Modal -->
    <div class="modal fade glass-modal" id="templatesModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i> إدارة قوالب واتساب</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="templatesContainer">
                        <!-- Templates will be loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="../../assets/js/app.js"></script>
    <script src="../../assets/js/ajax-handler.js"></script>

    <script>
        // Initialize
        document.addEventListener('DOMContentLoaded', function () {
            loadSystemInfo();
            loadSystemLogs();
            initColorPickers();

            // Load settings for each section
            loadGeneralSettings();
            loadAppearanceSettings();
            loadFinancialSettings();
            loadWhatsAppSettings();
        });

        // Show/Hide Sections
        function showSection(section) {
            // Hide all sections
            document.querySelectorAll('.settings-section').forEach(s => {
                s.classList.remove('active');
            });

            // Remove active class from all nav links
            document.querySelectorAll('.settings-nav .nav-link').forEach(link => {
                link.classList.remove('active');
            });

            // Show selected section
            const sectionElement = document.getElementById(section + '-settings');
            if (sectionElement) {
                sectionElement.classList.add('active');
            }

            // Add active class to clicked link
            event.target.classList.add('active');
        }

        // Initialize Color Pickers
        function initColorPickers() {
            const primaryColor = document.getElementById('primaryColor');
            const primaryColorHex = document.getElementById('primaryColorHex');
            const primaryColorPreview = document.getElementById('primaryColorPreview');

            const secondaryColor = document.getElementById('secondaryColor');
            const secondaryColorHex = document.getElementById('secondaryColorHex');
            const secondaryColorPreview = document.getElementById('secondaryColorPreview');

            // Update color preview and hex value
            primaryColor.addEventListener('input', function () {
                primaryColorPreview.style.background = this.value;
                primaryColorHex.value = this.value;
            });

            secondaryColor.addEventListener('input', function () {
                secondaryColorPreview.style.background = this.value;
                secondaryColorHex.value = this.value;
            });

            // Update color input when hex value changes
            primaryColorHex.addEventListener('input', function () {
                primaryColor.value = this.value;
                primaryColorPreview.style.background = this.value;
            });

            secondaryColorHex.addEventListener('input', function () {
                secondaryColor.value = this.value;
                secondaryColorPreview.style.background = this.value;
            });
        }

        // Load System Information
        async function loadSystemInfo() {
            try {
                const response = await fetch('../../api/system.php?action=get_info');
                const data = await response.json();

                if (data.success) {
                    document.getElementById('osInfo').textContent = data.info.os;
                    document.getElementById('mysqlVersion').textContent = data.info.mysql;
                    document.getElementById('diskInfo').textContent = data.info.disk;
                    document.getElementById('memoryInfo').textContent = data.info.memory;
                    document.getElementById('installDate').textContent = data.info.install_date;
                    document.getElementById('lastUpdate').textContent = data.info.last_update;
                    document.getElementById('totalUsers').textContent = data.info.total_users;
                    document.getElementById('totalCars').textContent = data.info.total_cars;
                }
            } catch (error) {
                console.error('Error loading system info:', error);
            }
        }

        // Load System Logs
        async function loadSystemLogs() {
            try {
                const response = await fetch('../../api/system.php?action=get_logs');
                const data = await response.json();

                if (data.success) {
                    let html = '';
                    data.logs.forEach(log => {
                        html += `
                    <tr>
                        <td>${log.created_at}</td>
                        <td><span class="badge bg-${log.type === 'error' ? 'danger' : log.type === 'warning' ? 'warning' : 'info'}">${log.type}</span></td>
                        <td>${log.message}</td>
                    </tr>`;
                    });

                    document.getElementById('systemLogs').innerHTML = html;
                }
            } catch (error) {
                console.error('Error loading system logs:', error);
            }
        }

        // Load General Settings
        async function loadGeneralSettings() {
            try {
                const response = await fetch('../../api/settings.php?action=get_general');
                const data = await response.json();

                if (data.success) {
                    const form = document.getElementById('generalSettingsForm');
                    for (const key in data.settings) {
                        const input = form.querySelector(`[name="${key}"]`);
                        if (input) {
                            if (input.type === 'checkbox') {
                                input.checked = data.settings[key] === '1' || data.settings[key] === 'true';
                            } else {
                                input.value = data.settings[key];
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Error loading general settings:', error);
            }
        }

        // Load Appearance Settings
        async function loadAppearanceSettings() {
            try {
                const response = await fetch('../../api/settings.php?action=get_appearance');
                const data = await response.json();

                if (data.success) {
                    const form = document.getElementById('appearanceSettingsForm');
                    for (const key in data.settings) {
                        const input = form.querySelector(`[name="${key}"]`);
                        if (input) {
                            if (input.type === 'checkbox') {
                                input.checked = data.settings[key] === '1' || data.settings[key] === 'true';
                            } else if (key === 'primary_color' || key === 'secondary_color') {
                                const colorInput = document.getElementById(key);
                                const hexInput = document.getElementById(key + 'Hex');
                                const preview = document.getElementById(key + 'Preview');

                                if (colorInput && hexInput && preview) {
                                    colorInput.value = data.settings[key];
                                    hexInput.value = data.settings[key];
                                    preview.style.background = data.settings[key];
                                }
                            } else {
                                input.value = data.settings[key];
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Error loading appearance settings:', error);
            }
        }

        // Load Financial Settings
        async function loadFinancialSettings() {
            try {
                const response = await fetch('../../api/settings.php?action=get_financial');
                const data = await response.json();

                if (data.success) {
                    const form = document.getElementById('financialSettingsForm');
                    for (const key in data.settings) {
                        const input = form.querySelector(`[name="${key}"]`);
                        if (input) {
                            if (input.type === 'checkbox') {
                                input.checked = data.settings[key] === '1' || data.settings[key] === 'true';
                            } else {
                                input.value = data.settings[key];
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Error loading financial settings:', error);
            }
        }

        // Load WhatsApp Settings
        async function loadWhatsAppSettings() {
            try {
                const response = await fetch('../../api/settings.php?action=get_whatsapp');
                const data = await response.json();

                if (data.success) {
                    const form = document.getElementById('whatsappSettingsForm');
                    for (const key in data.settings) {
                        const input = form.querySelector(`[name="${key}"]`);
                        if (input) {
                            if (input.type === 'checkbox') {
                                input.checked = data.settings[key] === '1' || data.settings[key] === 'true';
                            } else {
                                input.value = data.settings[key];
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Error loading WhatsApp settings:', error);
            }
        }

        // Save General Settings
        document.getElementById('generalSettingsForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'save_general');

            try {
                const response = await fetch('../../api/settings.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    ajaxHandler.showSuccess('تم حفظ الإعدادات العامة بنجاح');
                } else {
                    ajaxHandler.showError(data.message);
                }
            } catch (error) {
                console.error('Error saving settings:', error);
                ajaxHandler.showError('حدث خطأ أثناء حفظ الإعدادات');
            }
        });

        // Save Appearance Settings
        document.getElementById('appearanceSettingsForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'save_appearance');

            try {
                const response = await fetch('../../api/settings.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    ajaxHandler.showSuccess('تم حفظ إعدادات المظهر بنجاح');

                    // Apply new colors immediately
                    const primaryColor = document.getElementById('primaryColor').value;
                    const secondaryColor = document.getElementById('secondaryColor').value;

                    document.documentElement.style.setProperty('--primary', primaryColor);
                    document.documentElement.style.setProperty('--secondary', secondaryColor);
                } else {
                    ajaxHandler.showError(data.message);
                }
            } catch (error) {
                console.error('Error saving appearance settings:', error);
                ajaxHandler.showError('حدث خطأ أثناء حفظ الإعدادات');
            }
        });

        // Save Financial Settings
        document.getElementById('financialSettingsForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'save_financial');

            try {
                const response = await fetch('../../api/settings.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    ajaxHandler.showSuccess('تم حفظ الإعدادات المالية بنجاح');
                } else {
                    ajaxHandler.showError(data.message);
                }
            } catch (error) {
                console.error('Error saving financial settings:', error);
                ajaxHandler.showError('حدث خطأ أثناء حفظ الإعدادات');
            }
        });

        // Save WhatsApp Settings
        document.getElementById('whatsappSettingsForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'save_whatsapp');

            try {
                const response = await fetch('../../api/settings.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    ajaxHandler.showSuccess('تم حفظ إعدادات واتساب بنجاح');
                } else {
                    ajaxHandler.showError(data.message);
                }
            } catch (error) {
                console.error('Error saving WhatsApp settings:', error);
                ajaxHandler.showError('حدث خطأ أثناء حفظ الإعدادات');
            }
        });

        // Test WhatsApp Connection
        async function testWhatsAppConnection() {
            try {
                const response = await fetch('../../api/whatsapp.php?action=test_connection');
                const data = await response.json();

                const resultDiv = document.getElementById('whatsappTestResult');

                if (data.success) {
                    resultDiv.className = 'api-test-result api-test-success';
                    resultDiv.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>✅ الاتصال ناجح</strong><br>
                    <small>${data.message}</small>
                `;
                } else {
                    resultDiv.className = 'api-test-result api-test-error';
                    resultDiv.innerHTML = `
                    <i class="fas fa-times-circle me-2"></i>
                    <strong>❌ فشل الاتصال</strong><br>
                    <small>${data.message}</small>
                `;
                }

                resultDiv.style.display = 'block';
            } catch (error) {
                console.error('Error testing WhatsApp connection:', error);
                ajaxHandler.showError('حدث خطأ أثناء اختبار الاتصال');
            }
        }

        // Copy Webhook URL
        function copyWebhookUrl() {
            const webhookUrl = document.getElementById('webhookUrl');
            webhookUrl.select();
            webhookUrl.setSelectionRange(0, 99999);

            navigator.clipboard.writeText(webhookUrl.value)
                .then(() => {
                    ajaxHandler.showSuccess('تم نسخ رابط Webhook');
                })
                .catch(err => {
                    ajaxHandler.showError('فشل نسخ الرابط');
                });
        }

        // Open Templates Modal
        function openTemplatesModal() {
            loadWhatsAppTemplates();
            $('#templatesModal').modal('show');
        }

        // Load WhatsApp Templates
        async function loadWhatsAppTemplates() {
            try {
                const response = await fetch('../../api/whatsapp.php?action=get_templates');
                const data = await response.json();

                if (data.success) {
                    let html = '<div class="row">';

                    data.templates.forEach(template => {
                        html += `
                    <div class="col-md-6 mb-3">
                        <div class="glass-card">
                            <h6>${template.name}</h6>
                            <small class="text-muted">${template.language === 'ar' ? 'العربية' : 'الإنجليزية'}</small>
                            
                            <div class="mt-2">
                                <textarea class="form-control glass-input" rows="4" readonly>${template.content}</textarea>
                            </div>
                            
                            <div class="mt-2">
                                <small><strong>المتغيرات:</strong> ${template.variables.join(', ')}</small>
                            </div>
                            
                            <div class="mt-3">
                                <button class="btn btn-sm btn-outline-warning" onclick="editTemplate(${template.id})">
                                    <i class="fas fa-edit me-1"></i> تعديل
                                </button>
                                <button class="btn btn-sm btn-outline-danger ms-2" onclick="deleteTemplate(${template.id})">
                                    <i class="fas fa-trash me-1"></i> حذف
                                </button>
                            </div>
                        </div>
                    </div>`;
                    });

                    html += '</div>';

                    // Add button to create new template
                    html += `
                <div class="text-center mt-3">
                    <button class="btn btn-orange" onclick="createNewTemplate()">
                        <i class="fas fa-plus me-2"></i> إنشاء قالب جديد
                    </button>
                </div>`;

                    document.getElementById('templatesContainer').innerHTML = html;
                }
            } catch (error) {
                console.error('Error loading templates:', error);
            }
        }

        // Reset Appearance
        function resetAppearance() {
            if (confirm('هل تريد إعادة تعيين إعدادات المظهر إلى القيم الافتراضية؟')) {
                document.getElementById('primaryColor').value = '#FF5722';
                document.getElementById('primaryColorHex').value = '#FF5722';
                document.getElementById('primaryColorPreview').style.background = '#FF5722';

                document.getElementById('secondaryColor').value = '#121212';
                document.getElementById('secondaryColorHex').value = '#121212';
                document.getElementById('secondaryColorPreview').style.background = '#121212';

                document.getElementById('darkMode').checked = true;
                document.getElementById('rtlMode').checked = true;
                document.getElementById('glassEffect').checked = true;
                document.getElementById('animations').checked = true;

                ajaxHandler.showSuccess('تم إعادة تعيين الإعدادات');
            }
        }

        // Save All Settings
        async function saveAllSettings() {
            const forms = [
                'generalSettingsForm',
                'appearanceSettingsForm',
                'financialSettingsForm',
                'whatsappSettingsForm'
            ];

            let allSuccess = true;

            for (const formId of forms) {
                const form = document.getElementById(formId);
                if (form) {
                    const formData = new FormData(form);
                    const action = formId.replace('Form', '').replace('Settings', '').toLowerCase();
                    formData.append('action', 'save_' + action);

                    try {
                        const response = await fetch('../../api/settings.php', {
                            method: 'POST',
                            body: formData
                        });

                        const data = await response.json();

                        if (!data.success) {
                            allSuccess = false;
                            console.error(`Failed to save ${action} settings:`, data.message);
                        }
                    } catch (error) {
                        allSuccess = false;
                        console.error(`Error saving ${action} settings:`, error);
                    }
                }
            }

            if (allSuccess) {
                ajaxHandler.showSuccess('تم حفظ جميع الإعدادات بنجاح');
            } else {
                ajaxHandler.showWarning('تم حفظ بعض الإعدادات مع وجود أخطاء');
            }
        }

        // Create System Backup
        async function createSystemBackup() {
            if (!confirm('هل تريد إنشاء نسخة احتياطية للنظام؟ قد تستغرق العملية بضع دقائق.')) {
                return;
            }

            try {
                const response = await fetch('../../api/system.php?action=create_backup');
                const data = await response.json();

                if (data.success) {
                    ajaxHandler.showSuccess('تم إنشاء النسخة الاحتياطية بنجاح');

                    if (data.download_url) {
                        setTimeout(() => {
                            window.open(data.download_url, '_blank');
                        }, 2000);
                    }
                } else {
                    ajaxHandler.showError(data.message);
                }
            } catch (error) {
                console.error('Error creating backup:', error);
                ajaxHandler.showError('حدث خطأ أثناء إنشاء النسخة الاحتياطية');
            }
        }

        // Clear System Logs
        async function clearSystemLogs() {
            if (!confirm('هل تريد مسح جميع سجلات النظام؟ لا يمكن التراجع عن هذا الإجراء.')) {
                return;
            }

            try {
                const response = await fetch('../../api/system.php?action=clear_logs');
                const data = await response.json();

                if (data.success) {
                    ajaxHandler.showSuccess('تم مسح سجلات النظام بنجاح');
                    loadSystemLogs();
                } else {
                    ajaxHandler.showError(data.message);
                }
            } catch (error) {
                console.error('Error clearing logs:', error);
                ajaxHandler.showError('حدث خطأ أثناء مسح السجلات');
            }
        }

        // Download System Logs
        async function downloadSystemLogs() {
            try {
                const response = await fetch('../../api/system.php?action=export_logs');

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `system_logs_${new Date().toISOString().split('T')[0]}.json`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                } else {
                    ajaxHandler.showError('فشل تحميل السجلات');
                }
            } catch (error) {
                console.error('Error downloading logs:', error);
                ajaxHandler.showError('حدث خطأ أثناء تحميل السجلات');
            }
        }

        // Create Backup
        async function createBackup() {
            try {
                const response = await fetch('../../api/system.php?action=backup', {
                    method: 'POST'
                });
                const data = await response.json();

                if (data.success) {
                    Toastify({
                        text: "تم إنشاء النسخة الاحتياطية بنجاح",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28a745"
                    }).showToast();
                } else {
                    Toastify({
                        text: data.message || "فشل إنشاء النسخة الاحتياطية",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#dc3545"
                    }).showToast();
                }
            } catch (error) {
                console.error('Error creating backup:', error);
            }
        }

        // Generate API Key
        function generateApiKey() {
            const key = 'sk_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
            document.querySelector('#api-settings input[readonly]').value = key;
            Toastify({
                text: "تم توليد مفتاح API جديد",
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#17a2b8"
            }).showToast();
        }

        // Clear System Logs
        function clearSystemLogs() {
            if (confirm('هل أنت متأكد من مسح جميع السجلات؟')) {
                document.getElementById('systemLogs').innerHTML = '<tr><td colspan="3" class="text-center">لا توجد سجلات</td></tr>';
                Toastify({
                    text: "تم مسح السجلات بنجاح",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28a745"
                }).showToast();
            }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="../../assets/js/ajax-handler.js"></script>
</body>

</html>