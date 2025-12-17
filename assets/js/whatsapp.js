// assets/js/whatsapp.js - WhatsApp Integration

/**
 * ============================================
 * WhatsApp Manager
 * ============================================
 */

class WhatsAppManager {
    constructor() {
        this.apiUrl = 'api/whatsapp.php';
        this.templates = {};
        this.loadTemplates();
    }

    /**
     * Load WhatsApp Message Templates
     */
    async loadTemplates() {
        try {
            const response = await fetch(this.apiUrl + '?action=get_templates');
            const data = await response.json();

            if (data.success) {
                this.templates = data.templates;
            }
        } catch (error) {
            console.error('Error loading WhatsApp templates:', error);
        }
    }

    /**
     * Send WhatsApp Message
     */
    async sendMessage(phone, message, type = 'text') {
        showLoading(','1J %13'D 'D13'D)...');

        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'send_message',
                    phone: phone,
                    message: message,
                    type: type
                })
            });

            const data = await response.json();
            hideLoading();

            if (data.success) {
                showToast('*E %13'D 'D13'D) (F,'-', 'success');
                return true;
            } else {
                showToast('A4D AJ %13'D 'D13'D): ' + data.message, 'error');
                return false;
            }
        } catch (error) {
            hideLoading();
            console.error('Error sending WhatsApp message:', error);
            showToast('-/+ .7# AJ %13'D 'D13'D)', 'error');
            return false;
        }
    }

    /**
     * Send Bulk WhatsApp Messages
     */
    async sendBulkMessage(recipients, message, type = 'text') {
        if (!recipients || recipients.length === 0) {
            showToast('DE J*E *-/J/ E3*DEJF', 'warning');
            return false;
        }

        showLoading(`,'1J %13'D 'D13'&D (0/${recipients.length})...`);

        let successCount = 0;
        let failCount = 0;

        try {
            for (let i = 0; i < recipients.length; i++) {
                const recipient = recipients[i];

                // Update progress
                const loadingOverlay = document.getElementById('loading-overlay');
                if (loadingOverlay) {
                    const progressText = loadingOverlay.querySelector('p');
                    if (progressText) {
                        progressText.textContent = `,'1J %13'D 'D13'&D (${i + 1}/${recipients.length})...`;
                    }
                }

                const result = await this.sendMessage(recipient.phone, message, type);

                if (result) {
                    successCount++;
                } else {
                    failCount++;
                }

                // Small delay between messages to avoid rate limiting
                await this.delay(1000);
            }

            hideLoading();

            if (successCount > 0) {
                showToast(`*E %13'D ${successCount} 13'D) (F,'-`, 'success');
            }

            if (failCount > 0) {
                showToast(`A4D %13'D ${failCount} 13'D)`, 'warning');
            }

            return true;
        } catch (error) {
            hideLoading();
            console.error('Error sending bulk messages:', error);
            showToast('-/+ .7# AJ %13'D 'D13'&D', 'error');
            return false;
        }
    }

    /**
     * Send Booking Confirmation
     */
    async sendBookingConfirmation(bookingId) {
        showLoading(','1J %13'D *#CJ/ 'D-,2...');

        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'send_booking_confirmation',
                    booking_id: bookingId
                })
            });

            const data = await response.json();
            hideLoading();

            if (data.success) {
                showToast('*E %13'D *#CJ/ 'D-,2 (F,'-', 'success');
                return true;
            } else {
                showToast('A4D AJ %13'D *#CJ/ 'D-,2: ' + data.message, 'error');
                return false;
            }
        } catch (error) {
            hideLoading();
            console.error('Error sending booking confirmation:', error);
            showToast('-/+ .7# AJ %13'D *#CJ/ 'D-,2', 'error');
            return false;
        }
    }

    /**
     * Send Payment Reminder
     */
    async sendPaymentReminder(customerId) {
        showLoading(','1J %13'D *0CJ1 'D/A9...');

        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'send_payment_reminder',
                    customer_id: customerId
                })
            });

            const data = await response.json();
            hideLoading();

            if (data.success) {
                showToast('*E %13'D *0CJ1 'D/A9 (F,'-', 'success');
                return true;
            } else {
                showToast('A4D AJ %13'D *0CJ1 'D/A9: ' + data.message, 'error');
                return false;
            }
        } catch (error) {
            hideLoading();
            console.error('Error sending payment reminder:', error);
            showToast('-/+ .7# AJ %13'D *0CJ1 'D/A9', 'error');
            return false;
        }
    }

    /**
     * Send Maintenance Alert
     */
    async sendMaintenanceAlert(carId) {
        showLoading(','1J %13'D *F(JG 'D5J'F)...');

        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'send_maintenance_alert',
                    car_id: carId
                })
            });

            const data = await response.json();
            hideLoading();

            if (data.success) {
                showToast('*E %13'D *F(JG 'D5J'F) (F,'-', 'success');
                return true;
            } else {
                showToast('A4D AJ %13'D *F(JG 'D5J'F): ' + data.message, 'error');
                return false;
            }
        } catch (error) {
            hideLoading();
            console.error('Error sending maintenance alert:', error);
            showToast('-/+ .7# AJ %13'D *F(JG 'D5J'F)', 'error');
            return false;
        }
    }

    /**
     * Send Welcome Message
     */
    async sendWelcomeMessage(customerId) {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'send_welcome_message',
                    customer_id: customerId
                })
            });

            const data = await response.json();
            return data.success;
        } catch (error) {
            console.error('Error sending welcome message:', error);
            return false;
        }
    }

    /**
     * Get Template by Type
     */
    getTemplate(type) {
        return this.templates[type] || null;
    }

    /**
     * Helper: Delay function
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

/**
 * ============================================
 * WhatsApp Modal Functions
 * ============================================
 */

// Global WhatsApp Manager Instance
const whatsappManager = new WhatsAppManager();

// Open WhatsApp Modal
function openWhatsAppModal() {
    const modal = document.getElementById('whatsappModal');
    if (modal) {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        // Load customers for select
        loadCustomersForWhatsApp();
    }
}

// Load Customers for WhatsApp
async function loadCustomersForWhatsApp() {
    try {
        const response = await fetch('api/customers.php?action=get_all');
        const data = await response.json();

        if (data.success) {
            const select = document.getElementById('specificCustomer');
            if (select) {
                let html = '<option value="">'.*1 9EJD</option>';
                data.customers.forEach(customer => {
                    html += `<option value="${customer.id}" data-phone="${customer.phone}">
                        ${customer.full_name} - ${customer.phone}
                    </option>`;
                });
                select.innerHTML = html;
            }
        }
    } catch (error) {
        console.error('Error loading customers:', error);
    }
}

// Handle Recipient Type Change
document.addEventListener('DOMContentLoaded', function() {
    const recipientSelect = document.getElementById('whatsappRecipient');
    const specificCustomerField = document.getElementById('specificCustomerField');

    if (recipientSelect) {
        recipientSelect.addEventListener('change', function() {
            if (this.value === 'specific') {
                specificCustomerField.style.display = 'block';
            } else {
                specificCustomerField.style.display = 'none';
            }
        });
    }

    // Handle Message Type Change
    const messageTypeSelect = document.getElementById('messageType');
    const customMessageField = document.getElementById('customMessageField');
    const templateField = document.getElementById('templateField');

    if (messageTypeSelect) {
        messageTypeSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customMessageField.style.display = 'block';
                templateField.style.display = 'none';
            } else {
                customMessageField.style.display = 'none';
                templateField.style.display = 'block';

                // Load template
                const template = whatsappManager.getTemplate(this.value);
                if (template && document.getElementById('customMessage')) {
                    document.getElementById('customMessage').value = template;
                }
            }
        });
    }

    // Handle WhatsApp Form Submit
    const whatsappForm = document.getElementById('whatsappForm');
    if (whatsappForm) {
        whatsappForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const recipientType = document.getElementById('whatsappRecipient').value;
            const message = document.getElementById('customMessage').value;
            let phoneNumber = document.getElementById('phoneNumber').value;

            if (!message) {
                showToast(''D1,'! %/.'D 'D13'D)', 'warning');
                return;
            }

            if (recipientType === 'specific') {
                const customerSelect = document.getElementById('specificCustomer');
                const selectedOption = customerSelect.options[customerSelect.selectedIndex];
                phoneNumber = selectedOption.getAttribute('data-phone');

                if (!phoneNumber) {
                    showToast(''D1,'! '.*J'1 9EJD', 'warning');
                    return;
                }
            }

            if (!phoneNumber && recipientType === 'specific') {
                showToast(''D1,'! %/.'D 1BE 'DG'*A', 'warning');
                return;
            }

            if (recipientType === 'all_customers' || recipientType === 'active_rentals') {
                // Send bulk message
                try {
                    const response = await fetch('api/customers.php?action=get_phones&type=' + recipientType);
                    const data = await response.json();

                    if (data.success) {
                        await whatsappManager.sendBulkMessage(data.customers, message);
                        closeModal('whatsappModal');
                        whatsappForm.reset();
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('-/+ .7#', 'error');
                }
            } else {
                // Send single message
                const success = await whatsappManager.sendMessage(phoneNumber, message);
                if (success) {
                    closeModal('whatsappModal');
                    whatsappForm.reset();
                }
            }
        });
    }
});

/**
 * ============================================
 * Quick WhatsApp Functions
 * ============================================
 */

// Send Quick WhatsApp Message
async function sendQuickWhatsApp(phone, message) {
    return await whatsappManager.sendMessage(phone, message);
}

// Send Booking WhatsApp
async function sendBookingWhatsApp(bookingId) {
    return await whatsappManager.sendBookingConfirmation(bookingId);
}

// Send Payment WhatsApp
async function sendPaymentWhatsApp(customerId) {
    return await whatsappManager.sendPaymentReminder(customerId);
}

// Send Maintenance WhatsApp
async function sendMaintenanceWhatsApp(carId) {
    return await whatsappManager.sendMaintenanceAlert(carId);
}

/**
 * ============================================
 * WhatsApp Web Integration
 * ============================================
 */

function openWhatsAppWeb(phone, message = '') {
    // Clean phone number (remove spaces, dashes, etc.)
    const cleanPhone = phone.replace(/[^0-9+]/g, '');

    // Encode message
    const encodedMessage = encodeURIComponent(message);

    // Open WhatsApp Web
    const url = `https://wa.me/${cleanPhone}?text=${encodedMessage}`;
    window.open(url, '_blank');
}

function openWhatsAppWebChat(phone) {
    const cleanPhone = phone.replace(/[^0-9+]/g, '');
    window.open(`https://wa.me/${cleanPhone}`, '_blank');
}

/**
 * ============================================
 * WhatsApp Status Checker
 * ============================================
 */

async function checkWhatsAppStatus() {
    try {
        const response = await fetch('api/whatsapp.php?action=check_status');
        const data = await response.json();

        if (data.success) {
            return data.status;
        }

        return false;
    } catch (error) {
        console.error('Error checking WhatsApp status:', error);
        return false;
    }
}

/**
 * ============================================
 * WhatsApp Templates
 * ============================================
 */

const WHATSAPP_TEMPLATES = {
    booking_confirmation: `E1-('K {customer_name} =K

4C1'K DC 9DI -,2 3J'1) E9F'!

*A'5JD 'D-,2:
=— 'D3J'1): {car_name}
=Å EF: {start_date}
=Å %DI: {end_date}
=° 'DE(D:: {amount} ª

F*7D9 D./E*C! <`,

    payment_reminder: `92J2J/92J2*J {customer_name} =K

F0C1CE (H,H/ E(D: E3*-B DD/A9:
=° 'DE(D:: {amount} ª
=Å *'1J. 'D'3*-B'B: {due_date}

J1,I 'D*H'5D E9F' D%*E'E 'D/A9.

4C1'K DC =O`,

    maintenance_alert: `*F(JG 5J'F) ='

'D3J'1) {car_name} *-*', %DI 5J'F):
=Ë FH9 'D5J'F): {maintenance_type}
=Å 'D*'1J. 'DE*HB9: {scheduled_date}
=° 'D*CDA) 'DEB/1): {estimated_cost} ª

J1,I ,/HD) EH9/ 'D5J'F) AJ #B1( HB*.`,

    promotional: `916 .'5! <‰

'-5D 9DI .5E {discount}% 9DI ,EJ9 -,H2'* 'D3J'1'*!

'D916 3'1J -*I {end_date}

'-,2 'D"F H'3*E*9 (#A6D 'D#39'1! =—=¨`,

    welcome: `E1-('K (C AJ F8'E *#,J1 'D3J'1'* 'DE*B/E! =—

F-F 39/'! ('F6E'EC %DJF'.

JECFC 'D"F:
 *5A- E,EH9) H'39) EF 'D3J'1'*
 -,2 3J'1*C 'DEA6D) (3GHD)
 **(9 -,H2'*C
 'D*H'5D E9F' E('41)

F*EFI DC *,1() 1'&9) E9F'! <`
};

console.log('WhatsApp.js loaded successfully');
