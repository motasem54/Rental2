/**
 * RentalSys - Main Application Script
 * Handles global functionality, UI interactions, and utility functions
 */

// Global Variables
let currentLocale = 'ar-SA';
let currencyCode = 'ILS'; // Israeli Shekel

/**
 * Format currency
 * @param {number} amount 
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat(currentLocale, {
        style: 'currency',
        currency: currencyCode,
        minimumFractionDigits: 0
    }).format(amount);
}

/**
 * Format date
 * @param {string} dateString 
 * @returns {string} Formatted date string
 */
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat(currentLocale, {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    }).format(date);
}

/**
 * Format date and time
 * @param {string} dateTimeString 
 * @returns {string} Formatted date and time string
 */
function formatDateTime(dateTimeString) {
    if (!dateTimeString) return '-';
    const date = new Date(dateTimeString);
    return new Intl.DateTimeFormat(currentLocale, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
}

/**
 * Format time
 * @param {string} timeString 
 * @returns {string} Formatted time string
 */
function formatTime(timeString) {
    if (!timeString) return '-';
    // Append dummy date to parse time
    const date = new Date(`2000-01-01T${timeString}`);
    return new Intl.DateTimeFormat(currentLocale, {
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
}

/**
 * Calculate time ago
 * @param {string} dateString 
 * @returns {string} Time ago string
 */
function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    let interval = seconds / 31536000;
    if (interval > 1) return Math.floor(interval) + " سنة";
    
    interval = seconds / 2592000;
    if (interval > 1) return Math.floor(interval) + " شهر";
    
    interval = seconds / 86400;
    if (interval > 1) return Math.floor(interval) + " يوم";
    
    interval = seconds / 3600;
    if (interval > 1) return Math.floor(interval) + " ساعة";
    
    interval = seconds / 60;
    if (interval > 1) return Math.floor(interval) + " دقيقة";
    
    return "منذ لحظات";
}

/**
 * Show toast notification
 * @param {string} message 
 * @param {string} type (success, error, warning, info)
 * @param {number} duration 
 */
function showToast(message, type = 'info', duration = 3000) {
    // Create toast container if not exists
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    
    // Generate unique ID
    const id = 'toast-' + Date.now();
    
    // Icon based on type
    let icon = 'info-circle';
    let headerClass = 'text-primary';
    
    if (type === 'success') {
        icon = 'check-circle';
        headerClass = 'text-success';
    } else if (type === 'error') {
        icon = 'exclamation-circle';
        headerClass = 'text-danger';
    } else if (type === 'warning') {
        icon = 'exclamation-triangle';
        headerClass = 'text-warning';
    }
    
    // Toast HTML
    const toastHtml = `
        <div id="${id}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header glass-header">
                <i class="fas fa-${icon} ${headerClass} me-2"></i>
                <strong class="me-auto">نظام التأجير</strong>
                <small>الآن</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body glass-body">
                ${message}
            </div>
        </div>
    `;
    
    // Append to container
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = toastHtml.trim();
    const toastElement = tempDiv.firstChild;
    container.appendChild(toastElement);
    
    // Initialize Bootstrap Toast
    const toast = new bootstrap.Toast(toastElement, { delay: duration });
    toast.show();
    
    // Remove after hidden
    toastElement.addEventListener('hidden.bs.toast', function () {
        toastElement.remove();
    });
}

/**
 * Show Alert Modal
 * @param {string} title 
 * @param {string} message 
 * @param {string} type 
 */
function showAlert(title, message, type = 'info') {
    // Use SweetAlert2 if available, otherwise fallback to Bootstrap Modal
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: title,
            text: message,
            icon: type,
            confirmButtonText: 'حسناً',
            customClass: {
                popup: 'glass-card',
                confirmButton: 'btn btn-primary'
            }
        });
    } else {
        alert(`${title}\n${message}`);
    }
}

/**
 * Show Confirmation Modal
 * @param {string} message 
 * @param {function} onConfirm 
 * @param {function} onCancel 
 */
function showConfirm(message, onConfirm, onCancel = null) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'نعم، نفذ الإجراء',
            cancelButtonText: 'إلغاء',
            customClass: {
                popup: 'glass-card'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (onConfirm) onConfirm();
            } else {
                if (onCancel) onCancel();
            }
        });
    } else {
        if (confirm(message)) {
            if (onConfirm) onConfirm();
        } else {
            if (onCancel) onCancel();
        }
    }
}

/**
 * Generic AJAX Request
 * @param {string} url 
 * @param {string} method 
 * @param {object} data 
 * @returns {Promise}
 */
async function ajaxRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    if (data) {
        if (data instanceof FormData) {
            options.body = data;
        } else {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(data);
        }
    }
    
    try {
        const response = await fetch(url, options);
        
        // Handle non-JSON responses (like HTML errors)
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            return await response.json();
        } else {
            const text = await response.text();
            console.error("Non-JSON response received:", text);
            // Don't throw immediately, try to parse or return text if needed, 
            // but for now let's assume it's an error if we expected JSON
            throw new Error("استجابة غير صالحة من الخادم (HTML received)");
        }
    } catch (error) {
        console.error('AJAX Error:', error);
        throw error;
    }
}

// Wrapper functions for AJAX
async function ajaxGet(url) {
    return ajaxRequest(url, 'GET');
}

async function ajaxPost(url, data) {
    return ajaxRequest(url, 'POST', data);
}

async function ajaxPut(url, data) {
    return ajaxRequest(url, 'PUT', data);
}

async function ajaxDelete(url) {
    return ajaxRequest(url, 'DELETE');
}

/**
 * Validate Form
 * @param {string} formId 
 * @returns {boolean}
 */
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('is-invalid');
            
            // Add error message if not exists
            let errorDiv = input.nextElementSibling;
            if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'هذا الحقل مطلوب';
                input.parentNode.insertBefore(errorDiv, input.nextSibling);
            }
        } else {
            input.classList.remove('is-invalid');
        }
        
        // Remove error on input
        input.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });
    });
    
    return isValid;
}

/**
 * Show Loading Overlay
 * @param {string} message 
 */
function showLoading(message = 'جاري التحميل...') {
    // Check if loading overlay already exists
    let overlay = document.querySelector('.loading-overlay');
    
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = `
            <div class="spinner-border text-light mb-3" role="status"></div>
            <div class="loading-text text-white">${message}</div>
        `;
        document.body.appendChild(overlay);
    } else {
        const textElement = overlay.querySelector('.loading-text');
        if (textElement) textElement.textContent = message;
    }
    
    // Show overlay
    setTimeout(() => {
        overlay.classList.add('active');
    }, 10);
}

/**
 * Hide Loading Overlay
 */
function hideLoading() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.classList.remove('active');
        setTimeout(() => {
            overlay.remove();
        }, 300);
    }
}

/**
 * Open Modal
 * @param {string} modalId 
 */
function openModal(modalId) {
    const modalElement = document.getElementById(modalId);
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}

/**
 * Close Modal
 * @param {string} modalId 
 */
function closeModal(modalId) {
    const modalElement = document.getElementById(modalId);
    if (modalElement) {
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }
    }
}

/**
 * Toggle Sidebar
 * Improved for mobile and desktop
 */
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const isMobile = window.innerWidth <= 767; // Mobile breakpoint
    
    if (!sidebar) return;

    if (isMobile) {
        // On mobile, toggle the 'show' class for slide-in effect
        sidebar.classList.toggle('show');
        
        // Add overlay when sidebar is open on mobile
        if (sidebar.classList.contains('show')) {
            // Create overlay if it doesn't exist
            let overlay = document.querySelector('.sidebar-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay';
                document.body.appendChild(overlay);
                
                // Close sidebar when clicking on overlay
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    document.body.removeChild(overlay);
                });
            }
        } else {
            // Remove overlay when closing sidebar
            const overlay = document.querySelector('.sidebar-overlay');
            if (overlay) {
                document.body.removeChild(overlay);
            }
        }
    } else {
        // On desktop, toggle the 'collapsed' class
        sidebar.classList.toggle('collapsed');
        document.body.classList.toggle('sidebar-collapsed');
    }
    
    // Save sidebar state
    const isCollapsed = sidebar.classList.contains('collapsed');
    localStorage.setItem('sidebarCollapsed', isCollapsed);
    
    // Dispatch custom event for other scripts
    const event = new CustomEvent('sidebarToggled', { 
        detail: { isCollapsed: isCollapsed } 
    });
    document.dispatchEvent(event);
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(e) {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.mobile-menu-toggle');
    
    if (!sidebar || !toggleBtn) return;

    if (window.innerWidth <= 768) {
        if (sidebar.classList.contains('show') && 
            !sidebar.contains(e.target) && 
            !toggleBtn.contains(e.target)) {
            toggleSidebar();
        }
    }
});

/**
 * Print Element
 * @param {string} elementId 
 */
function printElement(elementId) {
    const content = document.getElementById(elementId).innerHTML;
    const originalContent = document.body.innerHTML;
    
    document.body.innerHTML = content;
    window.print();
    document.body.innerHTML = originalContent;
    
    // Re-attach event listeners (reload page is safer)
    window.location.reload();
}

/**
 * Export Table to CSV
 * @param {array} data 
 * @param {string} filename 
 */
function exportToCSV(data, filename = 'export.csv') {
    if (!data || !data.length) {
        showToast('لا توجد بيانات للتصدير', 'warning');
        return;
    }
    
    const headers = Object.keys(data[0]);
    const csvContent = [
        headers.join(','),
        ...data.map(row => headers.map(fieldName => JSON.stringify(row[fieldName])).join(','))
    ].join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

/**
 * Copy to Clipboard
 * @param {string} text 
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('تم النسخ للحافظة', 'success');
    }).catch(err => {
        console.error('Failed to copy: ', err);
        showToast('فشل النسخ', 'error');
    });
}

/**
 * Search Table
 * @param {string} inputId 
 * @param {string} tableId 
 */
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    
    if (!input || !table) return;
    
    input.addEventListener('keyup', function() {
        const filter = input.value.toLowerCase();
        const rows = table.getElementsByTagName('tr');
        
        for (let i = 1; i < rows.length; i++) { // Start from 1 to skip header
            let found = false;
            const cells = rows[i].getElementsByTagName('td');
            
            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];
                if (cell) {
                    const textValue = cell.textContent || cell.innerText;
                    if (textValue.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            
            rows[i].style.display = found ? '' : 'none';
        }
    });
}

/**
 * Local Storage Helpers
 */
function setStorage(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
}

function getStorage(key) {
    const value = localStorage.getItem(key);
    return value ? JSON.parse(value) : null;
}

function removeStorage(key) {
    localStorage.removeItem(key);
}

/**
 * Debounce Function
 * Limits the rate at which a function can fire
 */
function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize Global Components
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Initialize Popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    });
    
    // Add glass effect to navbar on scroll
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }
    });
});

// Quick Actions Handler
function quickAction(action) {
    switch(action) {
        case 'new_booking':
            window.location.href = 'modules/bookings/create.php';
            break;
        case 'add_car':
            window.location.href = 'modules/cars/index.php?action=add';
            break;
        case 'add_customer':
            window.location.href = 'modules/customers/index.php?action=add';
            break;
        case 'return_car':
            window.location.href = 'modules/rentals/return.php';
            break;
        default:
            console.log('Unknown action:', action);
    }
}

// System Backup
async function createBackup() {
    try {
        showLoading('جاري إنشاء نسخة احتياطية...');
        const result = await ajaxPost('api/system.php', { action: 'backup' });
        
        if (result.success) {
            showToast('تم إنشاء النسخة الاحتياطية بنجاح', 'success');
        } else {
            showToast(result.message || 'فشل إنشاء النسخة الاحتياطية', 'error');
        }
    } catch (error) {
        showToast('حدث خطأ أثناء النسخ الاحتياطي', 'error');
    } finally {
        hideLoading();
    }
}

// WhatsApp Integration
function viewBooking(bookingId) {
    window.location.href = `modules/bookings/view.php?id=${bookingId}`;
}

async function sendWhatsAppReminder(bookingId) {
    try {
        const result = await ajaxPost('api/whatsapp.php', { 
            action: 'send_reminder',
            booking_id: bookingId 
        });
        
        if (result.success) {
            showToast('تم إرسال التذكير بنجاح', 'success');
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('فشل إرسال التذكير', 'error');
    }
}

// Activity Log
async function loadActivityLog() {
    const container = document.getElementById('activityLog');
    if (!container) return;
    
    try {
        const logs = await ajaxGet('api/logs.php?limit=5');
        
        if (logs && logs.length > 0) {
            container.innerHTML = logs.map(log => `
                <div class="activity-item d-flex align-items-center mb-3">
                    <div class="activity-icon bg-light rounded-circle p-2 me-3">
                        <i class="fas fa-history text-primary"></i>
                    </div>
                    <div class="activity-content">
                        <h6 class="mb-0">${log.action}</h6>
                        <small class="text-muted">${timeAgo(log.created_at)}</small>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<p class="text-center text-muted">لا توجد نشاطات حديثة</p>';
        }
    } catch (error) {
        console.error('Error loading logs:', error);
    }
}
