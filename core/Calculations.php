<?php
// core/Calculations.php

class RentalCalculations {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * حساب تكلفة التأجير بالساعات
     */
    public function calculateHourlyRental($car_id, $start_datetime, $end_datetime, $insurance_type = 'third_party') {
        // الحصول على سعر السيارة
        $car = $this->getCarDetails($car_id);
        
        // حساب الفرق بالدقائق
        $start = new DateTime($start_datetime);
        $end = new DateTime($end_datetime);
        $interval = $start->diff($end);
        
        $total_minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
        
        // تحويل الدقائق إلى ساعات (تقريب للأعلى)
        $total_hours = ceil($total_minutes / 60);
        
        // الحد الأدنى للتأجير: 3 ساعات
        if ($total_hours < 3) {
            $total_hours = 3;
        }
        
        // حساب السعر حسب الساعات
        $hourly_rate = $car['daily_rate'] / 24; // السعر بالساعة
        $base_cost = $hourly_rate * $total_hours;
        
        // حساب تكلفة الصيانة
        $maintenance_cost_per_hour = $car['maintenance_cost_per_day'] / 24;
        $maintenance_cost = $maintenance_cost_per_hour * $total_hours;
        
        // حساب التأمين
        $insurance_cost = $this->calculateInsuranceCost($car['insurance_type'], $insurance_type, $base_cost, $total_hours);
        
        // حساب الضريبة
        $subtotal = $base_cost + $maintenance_cost + $insurance_cost;
        $tax_rate = $this->getSetting('tax_rate', 17);
        $tax = $subtotal * ($tax_rate / 100);
        
        // الإجمالي
        $total = $subtotal + $tax;
        
        // حساب الدفعة المقدمة
        $deposit_percentage = $this->getSetting('deposit_percentage', 40);
        $deposit = $total * ($deposit_percentage / 100);
        
        return [
            'total_hours' => $total_hours,
            'total_minutes' => $total_minutes,
            'hourly_rate' => round($hourly_rate, 2),
            'base_cost' => round($base_cost, 2),
            'maintenance_cost' => round($maintenance_cost, 2),
            'insurance_cost' => round($insurance_cost, 2),
            'subtotal' => round($subtotal, 2),
            'tax_rate' => $tax_rate,
            'tax' => round($tax, 2),
            'total' => round($total, 2),
            'deposit' => round($deposit, 2),
            'start_time' => $start_datetime,
            'end_time' => $end_datetime,
            'car_details' => $car
        ];
    }
    
    /**
     * حساب رسوم التأخير بالساعات
     */
    public function calculateLateReturnFees($rental_id, $actual_return_datetime) {
        // الحصول على بيانات العملية
        $rental = $this->getRentalDetails($rental_id);
        
        if (!$rental) {
            return ['error' => 'عملية التأجير غير موجودة'];
        }
        
        $scheduled_return = new DateTime($rental['end_date'] . ' ' . $rental['return_time']);
        $actual_return = new DateTime($actual_return_datetime);
        
        // إذا كان الإرجاع في الوقت المحدد أو قبله
        if ($actual_return <= $scheduled_return) {
            return [
                'late_hours' => 0,
                'late_minutes' => 0,
                'late_fee_per_hour' => 0,
                'total_late_fee' => 0,
                'is_late' => false,
                'message' => 'تم الإرجاع في الموعد المحدد'
            ];
        }
        
        // حساب الفرق بالدقائق
        $interval = $scheduled_return->diff($actual_return);
        $late_minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
        
        // ساعة السماح المجانية
        $grace_period = 60; // 60 دقيقة (ساعة واحدة)
        
        if ($late_minutes <= $grace_period) {
            return [
                'late_hours' => 0,
                'late_minutes' => $late_minutes,
                'late_fee_per_hour' => 0,
                'total_late_fee' => 0,
                'is_late' => false,
                'message' => 'ساعة سماح مجانية'
            ];
        }
        
        // خصم ساعة السماح
        $late_minutes -= $grace_period;
        
        // تحويل الدقائق إلى ساعات (تقريب للأعلى)
        $late_hours = ceil($late_minutes / 60);
        
        // الحصول على رسوم التأخير من الإعدادات
        $late_fee_per_hour = $this->getSetting('late_fee_per_hour', 50);
        
        // حساب رسوم التأخير
        $total_late_fee = $late_hours * $late_fee_per_hour;
        
        // حد أقصى لرسوم التأخير (يوم كامل)
        $max_late_fee = $rental['daily_rate'];
        if ($total_late_fee > $max_late_fee) {
            $total_late_fee = $max_late_fee;
            $late_hours = 24; // تعتبر يوم كامل
        }
        
        return [
            'late_hours' => $late_hours,
            'late_minutes' => $late_minutes,
            'late_fee_per_hour' => $late_fee_per_hour,
            'total_late_fee' => round($total_late_fee, 2),
            'is_late' => true,
            'scheduled_return' => $rental['end_date'] . ' ' . $rental['return_time'],
            'actual_return' => $actual_return_datetime,
            'grace_period_used' => $grace_period,
            'message' => 'يوجد تأخير في الإرجاع'
        ];
    }
    
    /**
     * حساب رسوم الكيلومترات الإضافية
     */
    public function calculateExtraKilometersFees($rental_id, $actual_kilometers) {
        $rental = $this->getRentalDetails($rental_id);
        
        if (!$rental) {
            return ['error' => 'عملية التأجير غير موجودة'];
        }
        
        $allowed_kilometers = $rental['km_allowed'] ?? 300; // افتراضي 300 كم
        $extra_km_rate = $rental['extra_km_rate'] ?? 2; // افتراضي 2 ريال للكم
        
        $pickup_km = $rental['pickup_km'] ?? 0;
        
        // حساب الكيلومترات المقطوعة
        $kilometers_driven = $actual_kilometers - $pickup_km;
        
        if ($kilometers_driven <= $allowed_kilometers) {
            return [
                'allowed_kilometers' => $allowed_kilometers,
                'actual_kilometers' => $kilometers_driven,
                'extra_kilometers' => 0,
                'extra_km_rate' => $extra_km_rate,
                'total_extra_km_fee' => 0,
                'has_extra_kilometers' => false,
                'message' => 'ضمن الحد المسموح'
            ];
        }
        
        $extra_kilometers = $kilometers_driven - $allowed_kilometers;
        $total_extra_km_fee = $extra_kilometers * $extra_km_rate;
        
        return [
            'allowed_kilometers' => $allowed_kilometers,
            'actual_kilometers' => $kilometers_driven,
            'extra_kilometers' => $extra_kilometers,
            'extra_km_rate' => $extra_km_rate,
            'total_extra_km_fee' => round($total_extra_km_fee, 2),
            'has_extra_kilometers' => true,
            'message' => 'يوجد كيلومترات إضافية'
        ];
    }
    
    /**
     * حساب جميع الرسوم الإضافية عند إرجاع السيارة
     */
    public function calculateAllReturnFees($rental_id, $return_data) {
        $fees = [];
        $total_extra_fees = 0;
        
        // 1. رسوم التأخير
        if (!empty($return_data['actual_return_datetime'])) {
            $late_fees = $this->calculateLateReturnFees($rental_id, $return_data['actual_return_datetime']);
            if ($late_fees['is_late']) {
                $fees['late_return'] = [
                    'type' => 'late_return',
                    'description' => 'رسوم تأخير إرجاع السيارة',
                    'hours' => $late_fees['late_hours'],
                    'rate' => $late_fees['late_fee_per_hour'],
                    'amount' => $late_fees['total_late_fee']
                ];
                $total_extra_fees += $late_fees['total_late_fee'];
            }
        }
        
        // 2. رسوم الكيلومترات الإضافية
        if (!empty($return_data['actual_kilometers'])) {
            $km_fees = $this->calculateExtraKilometersFees($rental_id, $return_data['actual_kilometers']);
            if ($km_fees['has_extra_kilometers']) {
                $fees['extra_kilometers'] = [
                    'type' => 'extra_kilometers',
                    'description' => 'رسوم كيلومترات إضافية',
                    'kilometers' => $km_fees['extra_kilometers'],
                    'rate' => $km_fees['extra_km_rate'],
                    'amount' => $km_fees['total_extra_km_fee']
                ];
                $total_extra_fees += $km_fees['total_extra_km_fee'];
            }
        }
        
        // 3. رسوم التنظيف
        if (!empty($return_data['cleaning_fee']) && $return_data['cleaning_fee'] > 0) {
            $cleaning_fee = floatval($return_data['cleaning_fee']);
            $fees['cleaning'] = [
                'type' => 'cleaning',
                'description' => 'رسوم تنظيف السيارة',
                'amount' => $cleaning_fee
            ];
            $total_extra_fees += $cleaning_fee;
        }
        
        // 4. رسوم الوقود
        if (!empty($return_data['fuel_fee']) && $return_data['fuel_fee'] > 0) {
            $fuel_fee = floatval($return_data['fuel_fee']);
            $fees['fuel'] = [
                'type' => 'fuel',
                'description' => 'رسوم تعبئة الوقود',
                'amount' => $fuel_fee
            ];
            $total_extra_fees += $fuel_fee;
        }
        
        // 5. رسوم الأضرار
        if (!empty($return_data['damage_fee']) && $return_data['damage_fee'] > 0) {
            $damage_fee = floatval($return_data['damage_fee']);
            $fees['damage'] = [
                'type' => 'damage',
                'description' => 'رسوم إصلاح أضرار',
                'amount' => $damage_fee
            ];
            $total_extra_fees += $damage_fee;
        }
        
        return [
            'fees' => $fees,
            'total_extra_fees' => round($total_extra_fees, 2),
            'has_extra_fees' => $total_extra_fees > 0
        ];
    }
    
    /**
     * حساب المبلغ النهائي بعد إضافة الرسوم الإضافية
     */
    public function calculateFinalAmount($rental_id, $extra_fees) {
        $rental = $this->getRentalDetails($rental_id);
        
        if (!$rental) {
            return ['error' => 'عملية التأجير غير موجودة'];
        }
        
        $original_total = $rental['total_rental_amount'];
        $total_paid = $this->getTotalPaid($rental_id);
        $remaining_original = $original_total - $total_paid;
        
        $total_with_fees = $original_total + $extra_fees['total_extra_fees'];
        $remaining_with_fees = $remaining_original + $extra_fees['total_extra_fees'];
        
        return [
            'original_total' => round($original_total, 2),
            'total_paid' => round($total_paid, 2),
            'remaining_original' => round($remaining_original, 2),
            'extra_fees' => round($extra_fees['total_extra_fees'], 2),
            'final_total' => round($total_with_fees, 2),
            'final_remaining' => round($remaining_with_fees, 2),
            'extra_fees_details' => $extra_fees['fees']
        ];
    }
    
    /**
     * الحصول على تفاصيل السيارة
     */
    private function getCarDetails($car_id) {
        $stmt = $this->db->prepare("SELECT * FROM cars WHERE id = ?");
        $stmt->execute([$car_id]);
        return $stmt->fetch();
    }
    
    /**
     * الحصول على تفاصيل عملية التأجير
     */
    private function getRentalDetails($rental_id) {
        $stmt = $this->db->prepare("SELECT * FROM rentals WHERE id = ?");
        $stmt->execute([$rental_id]);
        return $stmt->fetch();
    }
    
    /**
     * حساب تكلفة التأمين
     */
    private function calculateInsuranceCost($car_insurance_type, $selected_type, $base_cost, $hours) {
        $rates = [
            'third_party' => 0.08,  // 8%
            'partial' => 0.12,      // 12%
            'full' => 0.15          // 15%
        ];
        
        $rate = $rates[$selected_type] ?? 0.08;
        
        // التأمين يحسب بنسبة من القيمة اليومية
        $daily_equivalent = ($base_cost / $hours) * 24;
        return ($daily_equivalent * $rate / 24) * $hours;
    }
    
    /**
     * الحصول على الإعدادات من قاعدة البيانات
     */
    private function getSetting($key, $default = null) {
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        
        if ($result) {
            return $result['setting_value'];
        }
        
        return $default;
    }
    
    /**
     * الحصول على إجمالي المبلغ المدفوع
     */
    private function getTotalPaid($rental_id) {
        $stmt = $this->db->prepare("SELECT SUM(amount) as total FROM payments WHERE rental_id = ? AND status = 'completed'");
        $stmt->execute([$rental_id]);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }
    
    /**
     * توليد تقرير مفصل بالحسابات
     */
    public function generateDetailedCalculationReport($rental_id) {
        $rental = $this->getRentalDetails($rental_id);
        
        if (!$rental) {
            return ['error' => 'عملية التأجير غير موجودة'];
        }
        
        // الحصول على جميع الحسابات
        $car = $this->getCarDetails($rental['car_id']);
        $payments = $this->getRentalPayments($rental_id);
        $extra_charges = $this->getExtraCharges($rental_id);
        
        $total_paid = array_sum(array_column($payments, 'amount'));
        $total_extra_charges = array_sum(array_column($extra_charges, 'amount'));
        
        $final_total = $rental['total_rental_amount'] + $total_extra_charges;
        $remaining = $final_total - $total_paid;
        
        // حساب ساعات التأجير الفعلية
        $start = new DateTime($rental['start_date'] . ' ' . $rental['pickup_time']);
        $end = new DateTime($rental['end_date'] . ' ' . $rental['return_time']);
        $interval = $start->diff($end);
        
        $total_hours = ($interval->days * 24) + $interval->h;
        $hourly_rate = $rental['daily_rate'] / 24;
        
        return [
            'rental_info' => [
                'rental_number' => $rental['rental_number'],
                'status' => $rental['rental_status'],
                'start_date' => $rental['start_date'],
                'end_date' => $rental['end_date'],
                'pickup_time' => $rental['pickup_time'],
                'return_time' => $rental['return_time'],
                'total_hours' => $total_hours,
                'hourly_rate' => round($hourly_rate, 2)
            ],
            'car_info' => [
                'brand' => $car['brand'],
                'model' => $car['model'],
                'plate_number' => $car['plate_number'],
                'daily_rate' => $car['daily_rate']
            ],
            'calculations' => [
                'base_cost' => round($rental['total_rental_amount'], 2),
                'deposit_paid' => round($rental['deposit_amount'], 2),
                'total_extra_charges' => round($total_extra_charges, 2),
                'final_total' => round($final_total, 2),
                'total_paid' => round($total_paid, 2),
                'remaining_amount' => round($remaining, 2)
            ],
            'payments' => $payments,
            'extra_charges' => $extra_charges,
            'breakdown' => $this->getCostBreakdown($rental)
        ];
    }
    
    /**
     * الحصول على دفعات العملية
     */
    private function getRentalPayments($rental_id) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE rental_id = ? ORDER BY payment_date");
        $stmt->execute([$rental_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * الحصول على الرسوم الإضافية
     */
    private function getExtraCharges($rental_id) {
        $stmt = $this->db->prepare("SELECT * FROM extra_charges WHERE rental_id = ? AND status = 'approved'");
        $stmt->execute([$rental_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * الحصول على تفصيل التكاليف
     */
    private function getCostBreakdown($rental) {
        return [
            'daily_rate' => $rental['daily_rate'],
            'rental_days' => $rental['rental_days'],
            'subtotal' => $rental['total_rental_amount'],
            'insurance_type' => $rental['insurance_type'],
            'insurance_cost' => $rental['insurance_cost'] ?? 0,
            'maintenance_cost' => $rental['maintenance_cost'] ?? 0,
            'tax_rate' => $rental['tax_rate'] ?? 17,
            'tax_amount' => $rental['tax'] ?? 0,
            'deposit_percentage' => 40,
            'deposit_amount' => $rental['deposit_amount']
        ];
    }
}
?>