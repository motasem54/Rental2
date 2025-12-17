<?php
// modules/rentals/print-contract.php
session_start();
require_once '../../config/database.php';
require_once '../../core/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$rental_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($rental_id == 0) {
    die('رقم العقد غير صحيح');
}

// Get rental details
$query = "SELECT r.*, c.first_name, c.last_name, c.id_number, c.phone, c.email, c.address,
          car.make, car.model, car.year, car.plate_number, car.daily_rate,
          u.full_name as created_by
          FROM rentals r
          JOIN customers c ON r.customer_id = c.id
          JOIN cars car ON r.car_id = car.id
          LEFT JOIN users u ON r.created_by = u.id
          WHERE r.id = ?";

$stmt = $pdo->prepare($query);
$stmt->execute([$rental_id]);
$rental = $stmt->fetch();

if (!$rental) {
    die('العقد غير موجود');
}

// Calculate totals
$days = ceil((strtotime($rental['end_date']) - strtotime($rental['start_date'])) / 86400);
$subtotal = $days * $rental['daily_rate'];
$tax = $subtotal * 0.17; // 17% VAT
$total = $subtotal + $tax;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عقد تأجير رقم #<?php echo $rental_id; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: white;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border: 2px solid #FF8C42;
            border-radius: 10px;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #FF8C42;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 2.5rem;
            color: #FF8C42;
            margin-bottom: 10px;
        }

        .header h1 {
            color: #FF8C42;
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .header p {
            color: #666;
            font-size: 0.9rem;
        }

        .contract-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .info-box h3 {
            color: #FF8C42;
            font-size: 1.1rem;
            margin-bottom: 15px;
            border-bottom: 2px solid #FF8C42;
            padding-bottom: 5px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #555;
        }

        .info-value {
            color: #333;
        }

        .car-details {
            background: linear-gradient(135deg, #FF8C42 0%, #FF6B35 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .car-details h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            border-bottom: 2px solid white;
            padding-bottom: 10px;
        }

        .car-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .car-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .car-icon {
            font-size: 1.2rem;
        }

        .pricing-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }

        .pricing-table th,
        .pricing-table td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #e0e0e0;
        }

        .pricing-table th {
            background: #FF8C42;
            color: white;
            font-weight: 600;
        }

        .pricing-table tr:hover {
            background: #f8f9fa;
        }

        .total-row {
            background: #f8f9fa;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .total-row td {
            padding: 15px 12px;
            border-top: 2px solid #FF8C42;
        }

        .terms {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .terms h3 {
            color: #FF8C42;
            margin-bottom: 15px;
        }

        .terms ul {
            list-style: none;
            padding: 0;
        }

        .terms li {
            padding: 8px 0;
            padding-right: 25px;
            position: relative;
        }

        .terms li:before {
            content: "✓";
            position: absolute;
            right: 0;
            color: #FF8C42;
            font-weight: bold;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 50px;
        }

        .signature-box {
            text-align: center;
        }

        .signature-line {
            border-top: 2px solid #333;
            margin-top: 60px;
            padding-top: 10px;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #FF8C42;
            color: #666;
            font-size: 0.9rem;
        }

        .print-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #FF8C42;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            box-shadow: 0 4px 15px rgba(255, 140, 66, 0.3);
            transition: all 0.3s ease;
        }

        .print-button:hover {
            background: #FF6B35;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 140, 66, 0.4);
        }

        @media print {
            .print-button {
                display: none;
            }
            
            body {
                padding: 0;
            }
            
            .container {
                border: none;
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .contract-info,
            .car-grid,
            .signatures {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">
        🖨️ طباعة العقد
    </button>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">🚗</div>
            <h1>عقد تأجير سيارة</h1>
            <p>رقم العقد: #<?php echo str_pad($rental_id, 6, '0', STR_PAD_LEFT); ?></p>
            <p>تاريخ الإصدار: <?php echo date('Y-m-d'); ?></p>
        </div>

        <!-- Contract Info -->
        <div class="contract-info">
            <!-- Customer Info -->
            <div class="info-box">
                <h3>👤 معلومات المستأجر</h3>
                <div class="info-row">
                    <span class="info-label">الاسم الكامل:</span>
                    <span class="info-value"><?php echo htmlspecialchars($rental['first_name'] . ' ' . $rental['last_name']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">رقم الهوية:</span>
                    <span class="info-value"><?php echo htmlspecialchars($rental['id_number']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">رقم الهاتف:</span>
                    <span class="info-value"><?php echo htmlspecialchars($rental['phone']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">البريد الإلكتروني:</span>
                    <span class="info-value"><?php echo htmlspecialchars($rental['email']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">العنوان:</span>
                    <span class="info-value"><?php echo htmlspecialchars($rental['address']); ?></span>
                </div>
            </div>

            <!-- Rental Period -->
            <div class="info-box">
                <h3>📅 فترة التأجير</h3>
                <div class="info-row">
                    <span class="info-label">تاريخ البدء:</span>
                    <span class="info-value"><?php echo date('Y-m-d', strtotime($rental['start_date'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">تاريخ الانتهاء:</span>
                    <span class="info-value"><?php echo date('Y-m-d', strtotime($rental['end_date'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">عدد الأيام:</span>
                    <span class="info-value"><?php echo $days; ?> يوم</span>
                </div>
                <div class="info-row">
                    <span class="info-label">حالة العقد:</span>
                    <span class="info-value">
                        <?php 
                        switch($rental['status']) {
                            case 'active': echo '✅ نشط'; break;
                            case 'completed': echo '✔️ مكتمل'; break;
                            case 'cancelled': echo '❌ ملغي'; break;
                            default: echo '⏳ قيد الانتظار';
                        }
                        ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">موظف الإصدار:</span>
                    <span class="info-value"><?php echo htmlspecialchars($rental['created_by']); ?></span>
                </div>
            </div>
        </div>

        <!-- Car Details -->
        <div class="car-details">
            <h3>🚙 تفاصيل السيارة</h3>
            <div class="car-grid">
                <div class="car-item">
                    <span class="car-icon">🏭</span>
                    <div>
                        <small>الماركة</small>
                        <div style="font-weight: bold;"><?php echo htmlspecialchars($rental['make']); ?></div>
                    </div>
                </div>
                <div class="car-item">
                    <span class="car-icon">🚗</span>
                    <div>
                        <small>الموديل</small>
                        <div style="font-weight: bold;"><?php echo htmlspecialchars($rental['model']); ?></div>
                    </div>
                </div>
                <div class="car-item">
                    <span class="car-icon">📅</span>
                    <div>
                        <small>سنة الصنع</small>
                        <div style="font-weight: bold;"><?php echo htmlspecialchars($rental['year']); ?></div>
                    </div>
                </div>
                <div class="car-item">
                    <span class="car-icon">🔢</span>
                    <div>
                        <small>رقم اللوحة</small>
                        <div style="font-weight: bold;"><?php echo htmlspecialchars($rental['plate_number']); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing -->
        <table class="pricing-table">
            <thead>
                <tr>
                    <th>البيان</th>
                    <th>الكمية</th>
                    <th>السعر</th>
                    <th>المجموع</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>إيجار يومي</td>
                    <td><?php echo $days; ?> يوم</td>
                    <td><?php echo number_format($rental['daily_rate'], 2); ?> ₪</td>
                    <td><?php echo number_format($subtotal, 2); ?> ₪</td>
                </tr>
                <tr>
                    <td>ضريبة القيمة المضافة (17%)</td>
                    <td>-</td>
                    <td>-</td>
                    <td><?php echo number_format($tax, 2); ?> ₪</td>
                </tr>
                <?php if ($rental['insurance_amount'] > 0): ?>
                <tr>
                    <td>التأمين</td>
                    <td>-</td>
                    <td>-</td>
                    <td><?php echo number_format($rental['insurance_amount'], 2); ?> ₪</td>
                </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td colspan="3">المجموع الإجمالي</td>
                    <td><?php echo number_format($rental['total_amount'], 2); ?> ₪</td>
                </tr>
            </tbody>
        </table>

        <!-- Terms and Conditions -->
        <div class="terms">
            <h3>📋 الشروط والأحكام</h3>
            <ul>
                <li>يجب على المستأجر تقديم رخصة قيادة سارية المفعول</li>
                <li>يتحمل المستأجر كامل المسؤولية عن السيارة خلال فترة التأجير</li>
                <li>يجب إعادة السيارة في الموعد المحدد وإلا سيتم فرض غرامة تأخير</li>
                <li>السيارة مؤمنة ضد الحوادث وفقاً لشروط التأمين المرفقة</li>
                <li>يمنع استخدام السيارة لأغراض غير قانونية</li>
                <li>يجب إبلاغ الشركة فوراً في حالة حدوث أي عطل أو حادث</li>
                <li>غرامة التأخير: 50 ₪ لكل ساعة تأخير</li>
                <li>يتم استرداد مبلغ التأمين بعد التأكد من سلامة السيارة</li>
            </ul>
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">
                    <strong>توقيع المستأجر</strong>
                    <p><?php echo htmlspecialchars($rental['first_name'] . ' ' . $rental['last_name']); ?></p>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    <strong>توقيع الشركة</strong>
                    <p>نظام تأجير السيارات</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>شركة تأجير السيارات</strong></p>
            <p>📞 الهاتف: +970-599-999999 | 📧 البريد: info@rental.com | 🌐 الموقع: www.rental.com</p>
            <p>تم الطباعة بتاريخ: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>

    <script>
        // Auto print on load (optional)
        // window.onload = function() { window.print(); };
    </script>
</body>
</html>