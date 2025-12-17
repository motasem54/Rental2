<?php
session_start();
require_once '../../config/database.php';
require_once '../../core/Auth.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

// Get rental ID from URL
$rental_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($rental_id == 0) {
    die('معرف الإيجار غير صحيح');
}

// Fetch rental details
$db = Database::getInstance();
$conn = $db->getConnection();

$query = "SELECT 
    r.*, 
    c.brand, c.model, c.year, c.plate_number, c.color,
    cu.full_name as customer_name, cu.email, cu.phone, cu.national_id,
    u.full_name as agent_name
FROM rentals r
LEFT JOIN cars c ON r.car_id = c.id
LEFT JOIN customers cu ON r.customer_id = cu.id
LEFT JOIN users u ON r.created_by = u.id
WHERE r.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $rental_id);
$stmt->execute();
$result = $stmt->get_result();
$rental = $result->fetch_assoc();

if (!$rental) {
    die('لم يتم العثور على بيانات الإيجار');
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عقد تأجير سيارة - رقم <?php echo $rental_id; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: #f8f9fa;
            padding: 20px;
        }

        .contract-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .contract-header {
            background: linear-gradient(135deg, #FF8C42 0%, #FF6B35 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .contract-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .contract-header p {
            margin: 0;
            opacity: 0.9;
        }

        .contract-body {
            padding: 40px;
        }

        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .section-title {
            background: rgba(255, 140, 66, 0.1);
            color: #FF6B35;
            padding: 10px 15px;
            border-right: 4px solid #FF6B35;
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .info-label {
            font-weight: 600;
            color: #5A6C7D;
            min-width: 120px;
        }

        .info-value {
            color: #2C3E50;
            font-weight: 500;
        }

        .car-damage-diagram {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            background: white;
            text-align: center;
            margin: 20px 0;
        }

        .car-diagram-img {
            max-width: 100%;
            height: auto;
            margin: 20px 0;
        }

        .damage-checkboxes {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 15px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .checkbox-item input[type="checkbox"] {
            margin-left: 10px;
            width: 20px;
            height: 20px;
        }

        .terms-section {
            background: #fff9f5;
            border: 1px solid #FFE4CC;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }

        .terms-section ol {
            padding-right: 20px;
            margin: 0;
        }

        .terms-section li {
            margin-bottom: 10px;
            line-height: 1.8;
        }

        .signatures {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: center;
            padding: 20px;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
        }

        .signature-line {
            border-top: 2px solid #2C3E50;
            width: 200px;
            margin: 60px auto 10px;
        }

        .signature-label {
            font-weight: 600;
            color: #2C3E50;
        }

        .print-buttons {
            text-align: center;
            margin: 20px 0;
        }

        .btn-print {
            background: linear-gradient(135deg, #FF8C42 0%, #FF6B35 100%);
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(255, 140, 66, 0.3);
            margin: 0 10px;
        }

        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 140, 66, 0.4);
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .print-buttons {
                display: none !important;
            }

            .contract-container {
                box-shadow: none;
                max-width: 100%;
            }

            .section {
                page-break-inside: avoid;
            }

            @page {
                margin: 1cm;
            }
        }

        @media (max-width: 768px) {

            .info-grid,
            .damage-checkboxes {
                grid-template-columns: 1fr;
            }

            .signatures {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="print-buttons">
        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print me-2"></i>
            طباعة العقد
        </button>
        <button class="btn-print" onclick="window.history.back()"
            style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
            <i class="fas fa-arrow-right me-2"></i>
            رجوع
        </button>
    </div>

    <div class="contract-container">
        <!-- Contract Header -->
        <div class="contract-header">
            <h1><i class="fas fa-file-contract me-2"></i> عقد تأجير سيارة</h1>
            <p>رقم العقد: <?php echo str_pad($rental_id, 6, '0', STR_PAD_LEFT); ?></p>
            <p>التاريخ: <?php echo date('Y/m/d'); ?></p>
        </div>

        <!-- Contract Body -->
        <div class="contract-body">
            <!-- Party Information -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-users me-2"></i>
                    معلومات الأطراف
                </div>

                <h6 class="mb-3">الطرف الأول (المؤجر):</h6>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">اسم الشركة:</span>
                        <span class="info-value">شركة تأجير السيارات المتقدمة</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">ممثل الشركة:</span>
                        <span
                            class="info-value"><?php echo htmlspecialchars($rental['agent_name'] ?? 'الإدارة'); ?></span>
                    </div>
                </div>

                <h6 class="mb-3 mt-4">الطرف الثاني (المستأجر):</h6>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">الاسم:</span>
                        <span class="info-value"><?php echo htmlspecialchars($rental['customer_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">رقم الهوية:</span>
                        <span
                            class="info-value"><?php echo htmlspecialchars($rental['national_id'] ?? 'غير محدد'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">الهاتف:</span>
                        <span class="info-value"><?php echo htmlspecialchars($rental['phone']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">البريد:</span>
                        <span class="info-value"><?php echo htmlspecialchars($rental['email']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Car Information -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-car me-2"></i>
                    معلومات السيارة
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">الماركة والموديل:</span>
                        <span
                            class="info-value"><?php echo htmlspecialchars($rental['brand'] . ' ' . $rental['model']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">سنة الصنع:</span>
                        <span class="info-value"><?php echo htmlspecialchars($rental['year']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">رقم اللوحة:</span>
                        <span class="info-value"><?php echo htmlspecialchars($rental['plate_number']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">اللون:</span>
                        <span class="info-value"><?php echo htmlspecialchars($rental['color'] ?? 'غير محدد'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Rental Period -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-calendar-alt me-2"></i>
                    فترة الإيجار والتكلفة
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">تاريخ البداية:</span>
                        <span class="info-value"><?php echo date('Y/m/d', strtotime($rental['start_date'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">تاريخ النهاية:</span>
                        <span class="info-value"><?php echo date('Y/m/d', strtotime($rental['end_date'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">التكلفة اليومية:</span>
                        <span class="info-value"><?php echo number_format($rental['daily_rate'], 2); ?> ₪</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">التكلفة الإجمالية:</span>
                        <span class="info-value"><strong><?php echo number_format($rental['total_amount'], 2); ?>
                                ₪</strong></span>
                    </div>
                </div>
            </div>

            <!-- Car Inspection Diagram -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-search-plus me-2"></i>
                    نموذج فحص حالة السيارة
                </div>

                <div class="car-damage-diagram">
                    <h6 class="mb-3">الرجاء فحص السيارة وتحديد أي أضرار موجودة</h6>
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 600'%3E%3Cg fill='none' stroke='%23000' stroke-width='2'%3E%3Crect x='250' y='150' width='300' height='180' rx='20'/%3E%3Cellipse cx='280' cy='240' rx='30' ry='30'/%3E%3Cellipse cx='520' cy='240' rx='30' ry='30'/%3E%3Crect x='290' y='160' width='220' height='80' rx='10'/%3E%3Crect x='260' y='180' width='40' height='40'/%3E%3Crect x='500' y='180' width='40' height='40'/%3E%3Cpath d='M250 250 L250 270 L280 290 L520 290 L550 270 L550 250'/%3E%3Ccircle cx='300' cy='200' r='3'/%3E%3Ccircle cx='500' cy='200' r='3'/%3E%3Ctext x='400' y='100' text-anchor='middle' font-size='20' fill='%23333'%3Eالأمام%3C/text%3E%3Ctext x='400' y='380' text-anchor='middle' font-size='20' fill='%23333'%3Eالخلف%3C/text%3E%3Ctext x='180' y='240' text-anchor='middle' font-size='20' fill='%23333'%3Eيمين%3C/text%3E%3Ctext x='620' y='240' text-anchor='middle' font-size='20' fill='%23333'%3Eيسار%3C/text%3E%3C/g%3E%3C/svg%3E"
                        alt="مخطط السيارة" class="car-diagram-img">

                    <div class="damage-checkboxes">
                        <div class="checkbox-item">
                            <input type="checkbox" id="front">
                            <label for="front">الواجهة الأمامية</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="rear">
                            <label for="rear">الواجهة الخلفية</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="right">
                            <label for="right">الجانب الأيمن</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="left">
                            <label for="left">الجانب الأيسر</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="hood">
                            <label for="hood">غطاء المحرك</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="roof">
                            <label for="roof">السقف</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="wheels">
                            <label for="wheels">الإطارات</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="interior">
                            <label for="interior">الداخلية</label>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label"><strong>ملاحظات الأضرار:</strong></label>
                        <textarea class="form-control" rows="3"
                            placeholder="اكتب أي ملاحظات إضافية عن حالة السيارة..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Terms and Conditions -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-file-alt me-2"></i>
                    الشروط والأحكام
                </div>

                <div class="terms-section">
                    <ol>
                        <li>يلتزم المستأجر بإعادة السيارة في التاريخ المحدد وبنفس الحالة التي استلمها بها.</li>
                        <li>المستأجر مسؤول عن أي أضرار تحدث للسيارة خلال فترة الإيجار.</li>
                        <li>يجب على المستأجر التأكد من وجود التأمين الساري المفعول.</li>
                        <li>في حالة التأخير عن موعد التسليم، سيتم احتساب رسوم إضافية.</li>
                        <li>يحق للمؤجر إنهاء العقد في حالة مخالفة الشروط.</li>
                        <li>المستأجر مسؤول عن جميع المخالفات المرورية خلال فترة الإيجار.</li>
                        <li>ممنوع التدخين داخل السيارة.</li>
                        <li>يجب إرجاع السيارة بخزان وقود ممتلئ كما تم استلامها.</li>
                    </ol>
                </div>
            </div>

            <!-- Signatures -->
            <div class="signatures">
                <div class="signature-box">
                    <div class="signature-label mb-2">توقيع المستأجر</div>
                    <div class="signature-line"></div>
                    <small class="text-muted"><?php echo htmlspecialchars($rental['customer_name']); ?></small>
                </div>

                <div class="signature-box">
                    <div class="signature-label mb-2">توقيع المؤجر</div>
                    <div class="signature-line"></div>
                    <small
                        class="text-muted"><?php echo htmlspecialchars($rental['agent_name'] ?? 'الإدارة'); ?></small>
                </div>
            </div>

            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-shield-alt me-1"></i>
                    هذا العقد ملزم قانونياً لجميع الأطراف
                </small>
            </div>
        </div>
    </div>

    <script>
        // Auto print on load if needed
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('print') === 'true') {
            setTimeout(() => window.print(), 500);
        }
    </script>
</body>

</html>