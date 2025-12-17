// assets/js/ajax-handler.js

class AjaxHandler {
    constructor() {
        // Get the base URL dynamically
        const path = window.location.pathname;
        const pathParts = path.split('/');
        // Find the root directory (usually the project folder)
        let baseUrl = window.location.origin + '/';

        // If we're in a subdirectory, find the api folder relative to current location
        if (path.includes('/modules/')) {
            this.baseURL = '../../api/';
        } else if (path.includes('/api/')) {
            this.baseURL = './';
        } else {
            this.baseURL = 'api/';
        }

        this.csrfToken = this.getCSRFToken();
        this.init();
    }

    init() {
        // منع إرسال الفورمات التقليدية
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.classList.contains('ajax-form') || form.hasAttribute('data-ajax')) {
                e.preventDefault();
                this.submitForm(form);
            }
        });

        // معالجة الأزرار ذات الصفة data-ajax
        document.addEventListener('click', (e) => {
            const button = e.target.closest('[data-ajax]');
            if (button) {
                e.preventDefault();
                this.handleAjaxButton(button);
            }
        });
    }

    getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    async submitForm(form) {
        const submitBtn = form.querySelector('[type="submit"]');
        const originalText = submitBtn?.innerHTML;

        // تعطيل الزر أثناء الإرسال
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> جاري المعالجة...';
        }

        try {
            const formData = new FormData(form);

            // إضافة CSRF token إذا وجد
            if (this.csrfToken) {
                formData.append('csrf_token', this.csrfToken);
            }

            const response = await fetch(form.action || this.baseURL + 'handler.php', {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            // عرض النتيجة
            this.showResponse(result, form);

            // إعادة تمكين الزر
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }

            // إعادة تعيين الفورم إذا نجحت العملية
            if (result.success && form.hasAttribute('data-reset')) {
                form.reset();
            }

            // إعادة التوجيه إذا مطلوب
            if (result.redirect) {
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 1500);
            }

            // إعادة تحميل الصفحة إذا مطلوب
            if (result.reload) {
                setTimeout(() => {
                    location.reload();
                }, 1500);
            }

            // تنفيذ callback إذا موجود
            if (result.callback && typeof window[result.callback] === 'function') {
                window[result.callback](result.data);
            }

        } catch (error) {
            console.error('Error submitting form:', error);
            this.showError('حدث خطأ في الاتصال بالخادم');

            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    }

    async handleAjaxButton(button) {
        const action = button.getAttribute('data-ajax');
        const url = button.getAttribute('data-url') || this.baseURL;
        const method = button.getAttribute('data-method') || 'POST';
        const confirmMsg = button.getAttribute('data-confirm');

        // طلب التأكيد إذا مطلوب
        if (confirmMsg && !confirm(confirmMsg)) {
            return;
        }

        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        try {
            const formData = new FormData();
            formData.append('action', action);

            // إضافة البيانات الإضافية
            const extraData = button.getAttribute('data-extra');
            if (extraData) {
                const data = JSON.parse(extraData);
                for (const key in data) {
                    formData.append(key, data[key]);
                }
            }

            const response = await fetch(url, {
                method: method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess(result.message || 'تمت العملية بنجاح');

                // إعادة تحميل البيانات إذا مطلوب
                if (button.hasAttribute('data-reload')) {
                    setTimeout(() => location.reload(), 1500);
                }

                // تحديث عنصر معين
                const updateElement = button.getAttribute('data-update');
                if (updateElement && result.html) {
                    document.querySelector(updateElement).innerHTML = result.html;
                }
            } else {
                this.showError(result.message || 'فشلت العملية');
            }
        } catch (error) {
            console.error('Error handling AJAX button:', error);
            this.showError('حدث خطأ في الاتصال');
        } finally {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    }

    async fetchData(url, params = {}) {
        try {
            const queryString = new URLSearchParams(params).toString();
            const fullUrl = url + (queryString ? '?' + queryString : '');

            const response = await fetch(fullUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            return await response.json();
        } catch (error) {
            console.error('Error fetching data:', error);
            return { success: false, message: 'خطأ في جلب البيانات' };
        }
    }

    async postData(url, data = {}) {
        try {
            const formData = new FormData();

            for (const key in data) {
                if (data[key] instanceof FileList) {
                    for (let i = 0; i < data[key].length; i++) {
                        formData.append(key + '[]', data[key][i]);
                    }
                } else if (Array.isArray(data[key])) {
                    data[key].forEach(item => {
                        formData.append(key + '[]', item);
                    });
                } else {
                    formData.append(key, data[key]);
                }
            }

            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            return await response.json();
        } catch (error) {
            console.error('Error posting data:', error);
            return { success: false, message: 'خطأ في إرسال البيانات' };
        }
    }

    showResponse(result, form = null) {
        if (result.success) {
            this.showSuccess(result.message);

            // إخفاء المودال إذا كان الفورم بداخله
            if (form && form.closest('.modal')) {
                setTimeout(() => {
                    $(form.closest('.modal')).modal('hide');
                }, 1500);
            }
        } else {
            this.showError(result.message);

            // عرض الأخطاء في الحقول
            if (result.errors && form) {
                this.showFieldErrors(form, result.errors);
            }
        }
    }

    showFieldErrors(form, errors) {
        // إزالة رسائل الخطأ السابقة
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        // إضافة رسائل الخطأ الجديدة
        for (const field in errors) {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');

                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = errors[field];

                input.parentNode.appendChild(errorDiv);
            }
        }

        // التركيز على أول حقل فيه خطأ
        const firstError = form.querySelector('.is-invalid');
        if (firstError) {
            firstError.focus();
        }
    }

    showSuccess(message) {
        Toastify({
            text: message,
            duration: 3000,
            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
            className: "success-toast",
            gravity: "top",
            position: "left",
            stopOnFocus: true
        }).showToast();
    }

    showError(message) {
        Toastify({
            text: message,
            duration: 5000,
            backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
            className: "error-toast",
            gravity: "top",
            position: "left",
            stopOnFocus: true
        }).showToast();
    }

    showInfo(message) {
        Toastify({
            text: message,
            duration: 3000,
            backgroundColor: "linear-gradient(to right, #2193b0, #6dd5ed)",
            className: "info-toast",
            gravity: "top",
            position: "left",
            stopOnFocus: true
        }).showToast();
    }

    showWarning(message) {
        Toastify({
            text: message,
            duration: 4000,
            backgroundColor: "linear-gradient(to right, #f7971e, #ffd200)",
            className: "warning-toast",
            gravity: "top",
            position: "left",
            stopOnFocus: true
        }).showToast();
    }

    // دالة للمساعدة في إنشاء الطلبات المعقدة
    async uploadFile(file, endpoint, progressCallback = null) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            const formData = new FormData();
            formData.append('file', file);
            formData.append('action', 'upload_file');

            xhr.open('POST', endpoint || this.baseURL + 'upload.php');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            if (progressCallback) {
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        progressCallback(percentComplete);
                    }
                });
            }

            xhr.onload = () => {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        resolve(response);
                    } catch (error) {
                        reject(new Error('Invalid JSON response'));
                    }
                } else {
                    reject(new Error(`Upload failed with status ${xhr.status}`));
                }
            };

            xhr.onerror = () => {
                reject(new Error('Network error'));
            };

            xhr.send(formData);
        });
    }

    // دالة للتحقق من صلاحية المدخلات
    validateForm(form) {
        const inputs = form.querySelectorAll('[required]');
        let isValid = true;
        const errors = {};

        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                errors[input.name] = 'هذا الحقل مطلوب';
            }

            // التحقق من صحة البريد الإلكتروني
            if (input.type === 'email' && input.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value)) {
                    isValid = false;
                    errors[input.name] = 'البريد الإلكتروني غير صالح';
                }
            }

            // التحقق من صحة رقم الهاتف
            if (input.type === 'tel' && input.value) {
                const phoneRegex = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/;
                if (!phoneRegex.test(input.value.replace(/\s/g, ''))) {
                    isValid = false;
                    errors[input.name] = 'رقم الهاتف غير صالح';
                }
            }
        });

        return { isValid, errors };
    }

    // دالة لإدارة الحالة أثناء التحميل
    setLoading(element, isLoading) {
        if (isLoading) {
            element.classList.add('loading');
            element.disabled = true;
            element.innerHTML = element.getAttribute('data-loading-text') || '<i class="fas fa-spinner fa-spin"></i>';
        } else {
            element.classList.remove('loading');
            element.disabled = false;
            element.innerHTML = element.getAttribute('data-original-text') || element.innerHTML;
        }
    }

    // دالة للتعامل مع الاستجابات الكبيرة (مثل التصدير)
    async downloadFile(url, filename) {
        try {
            const response = await fetch(url);
            const blob = await response.blob();
            const downloadUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = downloadUrl;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(downloadUrl);
        } catch (error) {
            console.error('Error downloading file:', error);
            this.showError('حدث خطأ أثناء تحميل الملف');
        }
    }
}

// تهيئة الـ AJAX Handler
const ajaxHandler = new AjaxHandler();

// دالة للاستخدام العام
async function ajaxRequest(url, data = {}, method = 'POST') {
    return await ajaxHandler.postData(url, data);
}

async function fetchData(url, params = {}) {
    return await ajaxHandler.fetchData(url, params);
}

// تصدير الدوال للاستخدام العام
window.ajaxHandler = ajaxHandler;
window.ajaxRequest = ajaxRequest;
window.fetchData = fetchData;

// دالة للمساعدة في إنشاء طلبات AJAX سريعة
async function quickAjax(action, data = {}) {
    const response = await ajaxHandler.postData(ajaxHandler.baseURL + 'handler.php', { action, ...data });

    if (response.success && response.notification) {
        ajaxHandler.showSuccess(response.notification);
    }

    return response;
}

// دالة للتحقق من الصلاحية قبل الإرسال
function validateBeforeSubmit(form) {
    const validation = ajaxHandler.validateForm(form);

    if (!validation.isValid) {
        ajaxHandler.showFieldErrors(form, validation.errors);
        return false;
    }

    return true;
}

// إضافة مستمع للأحداث للنماذج
document.addEventListener('DOMContentLoaded', function () {
    // إضافة مستمع لتنظيف رسائل الخطأ عند الكتابة
    document.querySelectorAll('input, textarea, select').forEach(input => {
        input.addEventListener('input', function () {
            this.classList.remove('is-invalid');
            const errorDiv = this.parentNode.querySelector('.invalid-feedback');
            if (errorDiv) {
                errorDiv.remove();
            }
        });
    });

    // إضافة مستمع لأزرار الإغلاق في المودالات
    document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', function () {
            const modal = this.closest('.modal');
            const form = modal.querySelector('form');
            if (form) {
                form.reset();
                form.querySelectorAll('.is-invalid').forEach(el => {
                    el.classList.remove('is-invalid');
                });
                form.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.remove();
                });
            }
        });
    });
});