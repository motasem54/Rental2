<?php
// modules/rentals/print-invoice.php
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
    die('رقم الفاتورة غير صحيح');
}

// Get rental and payment details
$query = "SELECT r.*, c.first_name, c.last_name, c.id_number, c.phone, c.email,
          car.make, car.model, car.year, car.plate_number, car.daily_rate,
          u.full_name as created_by,
          p.amount as paid_amount, p.payment_method, p.payment_date, p.transaction_id
          FROM rentals r
          JOIN customers c ON r.customer_id = c.id
          JOIN cars car ON r.car_id = car.id
          LEFT JOIN users u ON r.created_by = u.id
          LEFT JOIN payments p ON r.id = p.rental_id
          WHERE r.id = ?";

$stmt = $pdo->prepare($query);
$stmt->execute([$rental_id]);
$rental = $stmt->fetch();

if (!$rental) {
    die('الفاتورة غير موجودة');
}

$days = ceil((strtotime($rental['end_date']) - strtotime($rental['start_date'])) / 86400);
$subtotal = $days * $rental['daily_rate'];
$tax = $subtotal * 0.17;
$insurance = $rental['insurance_amount'] ?? 0;
$total = $rental['total_amount'];
$paid = $rental['paid_amount'] ?? 0;
$remaining = $total - $paid;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة رقم #<?php echo $rental_id; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
            padding: 20px;
        }

        .invoice-container {
            max-width: 850px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
        }

        .invoice-header {
            background: linear-gradient(135deg, #FF8C42 0%, #FF6B35 100%);
            color: white;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }

        .invoice-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .company-info {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 30px;
        }

        .company-logo {
            font-size: 3rem;
        }

        .company-details h1 {
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .invoice-title {
            background: white;
            color: #FF8C42;
            padding: 15px 30px;
            border-radius: 50px;
            display: inline-block;
            font-size: 1.5rem;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .invoice-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .invoice-body {
            padding: 40px;
        }

        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .info-box {
            padding: 20px;
            border: 2px solid #f0f0f0;
            border-radius: 10px;
            background: #fafafa;
        }

        .info-box h3 {
            color: #FF8C42;
            margin-bottom: 15px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #666;
            font-size: 0.9rem;
        }

        .info-value {
            font-weight: 600;
            color: #333;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table thead {
            background: #FF8C42;
            color: white;
        }

        .items-table th,
        .items-table td {
            padding: 15px;
            text-align: right;
            border-bottom: 1px solid #e0e0e0;
        }

        .items-table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .items-table tbody tr:hover {
            background: #f8f9fa;
        }

        .totals-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 1rem;
        }

        .totals-row.subtotal {
            border-bottom: 1px solid #ddd;
        }

        .totals-row.total {
            font-size: 1.4rem;
            font-weight: bold;
            color: #FF8C42;
            padding-top: 15px;
            border-top: 3px solid #FF8C42;
        }

        .payment-status {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .payment-status.paid {
            background: #d4edda;
            color: #155724;
            border: 2px solid #28a745;
        }

        .payment-status.partial {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffc107;
        }

        .payment-status.unpaid {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #dc3545;
        }

        .payment-info {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 10px;
            border-right: 4px solid #2196F3;
            margin-bottom: 30px;
        }

        .payment-info h4 {
            color: #1976D2;
            margin-bottom: 15px;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .payment-item {
            text-align: center;
        }

        .payment-item-label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 5px;
        }

        .payment-item-value {
            font-weight: bold;
            font-size: 1.1rem;
            color: #1976D2;
        }

        .notes-section {
            background: #fff9e6;
            padding: 20px;
            border-radius: 10px;
            border-right: 4px solid #ffc107;
            margin-bottom: 30px;
        }

        .notes-section h4 {
            color: #f57c00;
            margin-bottom: 10px;
        }

        .invoice-footer {
            background: #f8f9fa;
            padding: 30px 40px;
            border-top: 3px solid #FF8C42;
            text-align: center;
        }

        .footer-info {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.8;
        }

        .print-button {
            position: fixed;
            bottom: 30px;
            left: 30px;
            background: #FF8C42;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(255, 140, 66, 0.4);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .print-button:hover {
            background: #FF6B35;
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(255, 140, 66, 0.5);
        }

        .badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .print-button {
                display: none;
            }

            .invoice-container {
                box-shadow: none;
            }
        }

        @media (max-width: 768px) {
            .info-section,
            .payment-grid {
                grid-template-columns: 1fr;
            }

            .invoice-header {
                padding: 20px;
            }

            .invoice-body {
                padding: 20px;
            }

            .company-info {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">
        🖨️ طباعة الفاتورة
    </button>

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="header-content">
                <div class="company-info">
                    <div>
                        <div class="company-logo">🚗</div>
                        <div class="company-details">
                            <h1>نظام تأجير السيارات</h1>
                            <p>خدمات تأجير احترافية</p>
                        </div>
                    </div>
                    <div style="text-align: left;">
                        <p>📞 +970-599-999999</p>
                        <p>📧 info@rental.com</p>
                        <p>🌐 www.rental.com</p>
                    </div>
                </div>
                
                <div style="text-align: center;">
                    <div class="invoice-title">فاتــورة</div>
                </div>

                <div class="invoice-meta">
                    <div>
                        <strong>رقم الفاتورة:</strong> #<?php echo str_pad($rental_id, 6, '0', STR_PAD_LEFT); ?>
                    </div>
                    <div>
                        <strong>تاريخ الإصدار:</strong> <?php echo date('Y-m-d'); ?>
                    </div>
                    <div>
                        <strong>تاريخ الاستحقاق:</strong> <?php echo date('Y-m-d', strtotime($rental['end_date'])); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="invoice-body">
            <!-- Info Section -->
            <div class="info-section">
                <div class="info-box">
                    <h3>
                        <span>👤</span>
                        <span>معلومات العميل</span>
                    </h3>
                    <div class="info-item">
                        <span class="info-label">الاسم:</span>
                        <span class="info-value"><?php echo htmlspecialchars($rental['first_name'] . ' ' . $rental['last_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">رقم الهوية:</span>
                        <span class="info-value"><?php echo htmlspecialchars($rental['id_number']); ?></span>
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

                <div class="info-box">
                    <h3>
                        <span>🚙</span>
                        <span>تفاصيل السيارة</span>
                    </h3>
                    <div class="info-item">
                        <span class="info-label">السيارة:</span>
                        <span class="info-value"><?php echo htmlspecialchars($rental['make'] . ' ' . $rental['model']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">السنة:</span>
                        <span class="info-value"><?php echo htmlspecialchars($rental['year']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">رقم اللوحة:</span>
                        <span class="info-value"><?php echo htmlspecialchars($rental['plate_number']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">فترة التأجير:</span>
                        <span class="info-value"><?php echo $days; ?> يوم</span>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th>البيان</th>
                        <th>من تاريخ</th>
                        <th>إلى تاريخ</th>
                        <th>الكمية</th>
                        <th>السعر</th>
                        <th>المجموع</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>إيجار سيارة <?php echo htmlspecialchars($rental['make'] . ' ' . $rental['model']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($rental['start_date'])); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($rental['end_date'])); ?></td>
                        <td><?php echo $days; ?> يوم</td>
                        <td><?php echo number_format($rental['daily_rate'], 2); ?> ₪</td>
                        <td><?php echo number_format($subtotal, 2); ?> ₪</td>
                    </tr>
                    <?php if ($insurance > 0): ?>
                    <tr>
                        <td colspan="5">التأمين الشامل</td>
                        <td><?php echo number_format($insurance, 2); ?> ₪</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Totals -->
            <div class="totals-section">
                <div class="totals-row subtotal">
                    <span>المجموع الفرعي:</span>
                    <span><?php echo number_format($subtotal, 2); ?> ₪</span>
                </div>
                <?php if ($insurance > 0): ?>
                <div class="totals-row">
                    <span>التأمين:</span>
                    <span><?php echo number_format($insurance, 2); ?> ₪</span>
                </div>
                <?php endif; ?>
                <div class="totals-row">
                    <span>ضريبة القيمة المضافة (17%):</span>
                    <span><?php echo number_format($tax, 2); ?> ₪</span>
                </div>
                <div class="totals-row total">
                    <span>المجموع الإجمالي:</span>
                    <span><?php echo number_format($total, 2); ?> ₪</span>
                </div>
            </div>

            <!-- Payment Status -->
            <?php
            $status_class = 'unpaid';
            $status_text = '❌ لم يتم الدفع';
            
            if ($paid >= $total) {
                $status_class = 'paid';
                $status_text = '✅ تم الدفع بالكامل';
            } elseif ($paid > 0) {
                $status_class = 'partial';
                $status_text = '⚠️ دفع جزئي';
            }
            ?>
            <div class="payment-status <?php echo $status_class; ?>">
                <?php echo $status_text; ?>
            </div>

            <!-- Payment Info -->
            <?php if ($paid > 0): ?>
            <div class="payment-info">
                <h4>💳 معلومات الدفع</h4>
                <div class="payment-grid">
                    <div class="payment-item">
                        <div class="payment-item-label">المبلغ المدفوع</div>
                        <div class="payment-item-value"><?php echo number_format($paid, 2); ?> ₪</div>
                    </div>
                    <div class="payment-item">
                        <div class="payment-item-label">المبلغ المتبقي</div>
                        <div class="payment-item-value"><?php echo number_format($remaining, 2); ?> ₪</div>
                    </div>
                    <div class="payment-item">
                        <div class="payment-item-label">طريقة الدفع</div>
                        <div class="payment-item-value">
                            <?php 
                            switch($rental['payment_method']) {
                                case 'cash': echo 'نقداً'; break;
                                case 'credit_card': echo 'بطاقة ائتمان'; break;
                                case 'bank_transfer': echo 'حوالة بنكية'; break;
                                default: echo '-';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php if ($rental['transaction_id']): ?>
                <div style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #90caf9;">
                    <small>رقم المعاملة: <strong><?php echo htmlspecialchars($rental['transaction_id']); ?></strong></small>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Notes -->
            <?php if (!empty($rental['notes'])): ?>
            <div class="notes-section">
                <h4>📝 ملاحظات</h4>
                <p><?php echo nl2br(htmlspecialchars($rental['notes'])); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="footer-info">
                <p><strong>شروط الدفع:</strong> يجب الدفع عند استلام السيارة أو حسب الاتفاق</p>
                <p><strong>ملاحظة:</strong> هذه الفاتورة صالحة إلكترونياً ولا تحتاج إلى ختم</p>
                <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">
                <p style="color: #999; font-size: 0.85rem;">
                    تم إصدار هذه الفاتورة بواسطة <?php echo htmlspecialchars($rental['created_by']); ?> | 
                    تاريخ الطباعة: <?php echo date('Y-m-d H:i:s'); ?>
                </p>
                <p style="margin-top: 15px; color: #FF8C42; font-weight: bold;">
                    شكراً لتعاملكم معنا ونتمنى لكم تجربة ممتعة
                </p>
            </div>
        </div>
    </div>
</body>
</html>