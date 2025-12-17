<?php
// core/WhatsAppAPI.php

class WhatsAppAPI
{
    private $access_token;
    private $phone_number_id;
    private $api_version = 'v18.0';

    public function __construct()
    {
        $this->access_token = WHATSAPP_TOKEN;
        $this->phone_number_id = WHATSAPP_PHONE_ID;
    }

    /**
     * Send booking confirmation message
     */
    public function sendBookingConfirmation($phone_number, $booking_details)
    {
        $phone = $this->formatPhoneNumber($phone_number);

        if (!$phone) {
            return ['success' => false, 'message' => 'رقم الهاتف غير صالح'];
        }

        $data = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "template",
            "template" => [
                "name" => "booking_confirmation",
                "language" => ["code" => "ar"],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $booking_details['booking_id']],
                            ["type" => "text", "text" => $booking_details['car_name']],
                            ["type" => "text", "text" => $booking_details['start_date']],
                            ["type" => "text", "text" => $booking_details['end_date']],
                            ["type" => "text", "text" => $booking_details['total'] . ' ₪']
                        ]
                    ]
                ]
            ]
        ];

        return $this->sendMessage($data);
    }

    /**
     * Send booking activation message
     */
    public function sendBookingActivation($phone_number, $booking_id)
    {
        $phone = $this->formatPhoneNumber($phone_number);

        $data = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "template",
            "template" => [
                "name" => "booking_activation",
                "language" => ["code" => "ar"],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $booking_id]
                        ]
                    ]
                ]
            ]
        ];

        return $this->sendMessage($data);
    }

    /**
     * Send booking completion message
     */
    public function sendBookingCompletion($phone_number, $booking_id)
    {
        $phone = $this->formatPhoneNumber($phone_number);

        $data = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "template",
            "template" => [
                "name" => "booking_completion",
                "language" => ["code" => "ar"],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $booking_id]
                        ]
                    ]
                ]
            ]
        ];

        return $this->sendMessage($data);
    }

    /**
     * Send payment reminder
     */
    public function sendPaymentReminder($phone_number, $booking_id, $amount)
    {
        $phone = $this->formatPhoneNumber($phone_number);

        $data = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "template",
            "template" => [
                "name" => "payment_reminder",
                "language" => ["code" => "ar"],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $booking_id],
                            ["type" => "text", "text" => $amount . ' ₪'],
                            ["type" => "text", "text" => date('Y-m-d')]
                        ]
                    ]
                ]
            ]
        ];

        return $this->sendMessage($data);
    }

    /**
     * Send maintenance alert
     */
    public function sendMaintenanceAlert($phone_number, $car_details, $maintenance_date)
    {
        $phone = $this->formatPhoneNumber($phone_number);

        $data = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "template",
            "template" => [
                "name" => "maintenance_alert",
                "language" => ["code" => "ar"],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $car_details['brand'] . ' ' . $car_details['model']],
                            ["type" => "text", "text" => $car_details['plate_number']],
                            ["type" => "text", "text" => $maintenance_date]
                        ]
                    ]
                ]
            ]
        ];

        return $this->sendMessage($data);
    }

    /**
     * Send custom message
     */
    public function sendCustomMessage($phone_number, $message)
    {
        $phone = $this->formatPhoneNumber($phone_number);

        $data = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "text",
            "text" => [
                "preview_url" => false,
                "body" => $message
            ]
        ];

        return $this->sendMessage($data);
    }

    /**
     * Send template message
     */
    public function sendTemplateMessage($phone_number, $template_name, $parameters = [], $language_code = "ar")
    {
        $phone = $this->formatPhoneNumber($phone_number);

        $components = [];
        if (!empty($parameters)) {
            $components[] = [
                "type" => "body",
                "parameters" => $parameters
            ];
        }

        $data = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "template",
            "template" => [
                "name" => $template_name,
                "language" => ["code" => $language_code],
                "components" => $components
            ]
        ];

        return $this->sendMessage($data);
    }

    /**
     * Send bulk messages
     */
    public function sendBulkMessages($recipients, $message, $template = null)
    {
        $results = [];

        foreach ($recipients as $recipient) {
            if ($template) {
                $result = $this->sendTemplateMessage($recipient['phone'], $template, $recipient['parameters']);
            } else {
                $result = $this->sendCustomMessage($recipient['phone'], $message);
            }

            $results[] = [
                'phone' => $recipient['phone'],
                'success' => $result['success'],
                'message' => $result['message']
            ];
        }

        return [
            'success' => true,
            'results' => $results
        ];
    }

    /**
     * Generic send message method
     */
    private function sendMessage($data)
    {
        $url = "https://graph.facebook.com/{$this->api_version}/{$this->phone_number_id}/messages";

        $headers = [
            'Authorization: Bearer ' . $this->access_token,
            'Content-Type: application/json'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $response_data = json_decode($response, true);

        if ($http_code === 200) {
            // Log successful message
            $this->logMessage($data['to'], $data['type'], $response_data['messages'][0]['id']);

            return [
                'success' => true,
                'message_id' => $response_data['messages'][0]['id'],
                'response' => $response_data
            ];
        } else {
            return [
                'success' => false,
                'error' => $response_data['error']['message'] ?? 'Unknown error',
                'code' => $http_code
            ];
        }
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber($phone)
    {
        // Remove any non-digit characters
        $phone = preg_replace('/\D/', '', $phone);

        // Add country code if missing (Palestine/Israel +972)
        if (strpos($phone, '0') === 0) {
            $phone = '972' . substr($phone, 1);
        } elseif (strlen($phone) === 9) {
            $phone = '972' . $phone;
        } elseif (strlen($phone) === 10 && strpos($phone, '0') === 0) {
            $phone = '972' . substr($phone, 1);
        }

        return $phone;
    }

    /**
     * Log message to database
     */
    private function logMessage($phone, $type, $message_id)
    {
        global $db;

        if (!isset($db)) {
            $database = Database::getInstance();
            $db = $database->getConnection();
        }

        $stmt = $db->prepare("
            INSERT INTO whatsapp_messages 
            (phone_number, message_type, message_id, status, sent_at) 
            VALUES (?, ?, ?, 'sent', NOW())
        ");

        $stmt->execute([$phone, $type, $message_id]);
    }

    /**
     * Handle incoming webhook
     */
    public function handleWebhook($data)
    {
        $entry = $data['entry'][0] ?? null;

        if (!$entry) {
            return ['success' => false, 'message' => 'Invalid webhook data'];
        }

        $changes = $entry['changes'][0] ?? [];
        $value = $changes['value'] ?? [];

        // Handle different webhook types
        if (isset($value['messages'])) {
            return $this->handleIncomingMessage($value);
        } elseif (isset($value['statuses'])) {
            return $this->handleMessageStatus($value);
        }

        return ['success' => false, 'message' => 'Unhandled webhook type'];
    }

    /**
     * Handle incoming message
     */
    private function handleIncomingMessage($value)
    {
        $messages = $value['messages'];

        foreach ($messages as $message) {
            $from = $message['from'];
            $type = $message['type'];
            $message_id = $message['id'];

            // Log incoming message
            $this->logIncomingMessage($from, $type, $message_id, $message);

            // Auto-reply based on message type
            $this->autoReply($from, $type, $message);
        }

        return ['success' => true, 'message' => 'Webhook processed'];
    }

    /**
     * Handle message status updates
     */
    private function handleMessageStatus($value)
    {
        $statuses = $value['statuses'];

        foreach ($statuses as $status) {
            $message_id = $status['id'];
            $status_type = $status['status'];
            $timestamp = $status['timestamp'];

            // Update message status in database
            $this->updateMessageStatus($message_id, $status_type, $timestamp);
        }

        return ['success' => true];
    }

    /**
     * Log incoming message
     */
    private function logIncomingMessage($from, $type, $message_id, $message_data)
    {
        global $db;

        if (!isset($db)) {
            $database = Database::getInstance();
            $db = $database->getConnection();
        }

        $stmt = $db->prepare("
            INSERT INTO whatsapp_incoming 
            (phone_number, message_type, message_id, message_data, received_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");

        $message_json = json_encode($message_data);
        $stmt->execute([$from, $type, $message_id, $message_json]);
    }

    /**
     * Update message status
     */
    private function updateMessageStatus($message_id, $status, $timestamp)
    {
        global $db;

        if (!isset($db)) {
            $database = Database::getInstance();
            $db = $database->getConnection();
        }

        $column = '';
        switch ($status) {
            case 'delivered':
                $column = 'delivered_at';
                break;
            case 'read':
                $column = 'read_at';
                break;
        }

        if ($column) {
            $date = date('Y-m-d H:i:s', $timestamp);
            $stmt = $db->prepare("UPDATE whatsapp_messages SET {$column} = ?, status = ? WHERE message_id = ?");
            $stmt->execute([$date, $status, $message_id]);
        }
    }

    /**
     * Auto-reply to incoming messages
     */
    private function autoReply($phone, $type, $message)
    {
        $reply_message = "";

        if ($type === 'text') {
            $text = strtolower($message['text']['body']);

            // Check for keywords and auto-reply
            if (strpos($text, 'حالة') !== false || strpos($text, 'status') !== false) {
                $reply_message = "يمكنك معرفة حالة حجزك عن طريق تطبيقنا أو التواصل مع خدمة العملاء على الرقم 123456789";
            } elseif (strpos($text, 'سعر') !== false || strpos($text, 'price') !== false) {
                $reply_message = "يمكنك معرفة أسعار سياراتنا عن طريق زيارة موقعنا الإلكتروني أو التطبيق";
            } elseif (strpos($text, 'شكر') !== false || strpos($text, 'thank') !== false) {
                $reply_message = "شكراً لك! نحن دائماً هنا لخدمتك. هل تحتاج إلى أي مساعدة أخرى؟";
            } else {
                $reply_message = "شكراً لتواصلك معنا. للاستفسارات يمكنك التواصل مع خدمة العملاء على الرقم 123456789";
            }
        } else {
            $reply_message = "شكراً لتواصلك معنا. للاستفسارات يمكنك التواصل مع خدمة العملاء على الرقم 123456789";
        }

        // Send auto-reply
        $this->sendCustomMessage($phone, $reply_message);
    }
}
?>