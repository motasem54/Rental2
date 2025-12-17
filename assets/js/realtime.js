// assets/js/realtime.js - Real-time Updates & Notifications

/**
 * ============================================
 * Real-time Update Manager
 * ============================================
 */

class RealtimeManager {
    constructor(refreshInterval = 30000) {
        this.refreshInterval = refreshInterval;
        this.intervals = {};
        this.isActive = true;
    }

    // Start real-time updates
    start() {
        console.log('Real-time updates started');
        this.isActive = true;

        // Update dashboard stats
        if (document.getElementById('statTotalCars')) {
            this.intervals.stats = setInterval(() => this.updateDashboardStats(), this.refreshInterval);
            this.updateDashboardStats(); // Initial load
        }

        // Update notifications
        if (document.getElementById('totalNotifications')) {
            this.intervals.notifications = setInterval(() => this.updateNotifications(), 60000);
            this.updateNotifications(); // Initial load
        }

        // Update activity log
        if (document.getElementById('activityBody')) {
            this.intervals.activity = setInterval(() => this.updateActivityLog(), 45000);
        }

        // Update recent bookings
        if (document.getElementById('recentBookingsBody')) {
            this.intervals.bookings = setInterval(() => this.updateRecentBookings(), this.refreshInterval);
        }

        // Update date/time
        this.intervals.datetime = setInterval(() => this.updateDateTime(), 1000);
        this.updateDateTime();
    }

    // Stop real-time updates
    stop() {
        console.log('Real-time updates stopped');
        this.isActive = false;
        Object.values(this.intervals).forEach(interval => clearInterval(interval));
        this.intervals = {};
    }

    // Update Dashboard Statistics
    async updateDashboardStats() {
        if (!this.isActive) return;

        try {
            const response = await fetch('api/dashboard.php?action=get_stats');
            const data = await response.json();

            if (data.success) {
                // Update stat cards
                this.updateElement('statTotalCars', data.stats.total_cars);
                this.updateElement('statActiveRentals', data.stats.active_rentals);
                this.updateElement('statMonthlyIncome', data.stats.monthly_income + ' ª');
                this.updateElement('statMaintenanceDue', data.stats.maintenance_due);

                // Update changes
                this.updateElement('carsChange',
                    data.stats.cars_today > 0 ? `+${data.stats.cars_today} 'DJHE` : 'D' *:JJ1');

                this.updateElement('rentalsChange',
                    data.stats.rentals_today > 0 ? `+${data.stats.rentals_today} 'DJHE` : 'D' *:JJ1');

                const incomeChange = data.stats.income_change || 0;
                this.updateElement('incomeChange',
                    incomeChange > 0 ? `+${incomeChange}% 9F 'D4G1 'DE'6J` :
                    incomeChange < 0 ? `${incomeChange}% 9F 'D4G1 'DE'6J` : 'D' *:JJ1');

                // Update notification badges
                this.updateElement('bookingNotifications', data.stats.pending_bookings);
                this.updateElement('maintenanceNotifications', data.stats.maintenance_due);
                this.updateElement('totalNotifications',
                    data.stats.pending_bookings + data.stats.maintenance_due);

                // Update system info
                this.updateElement('dbSize', data.stats.db_size + ' MB');
                this.updateElement('uptime', data.stats.uptime + ' JHE');
                this.updateElement('systemPerformance', data.stats.performance + '%');

                const performanceBar = document.getElementById('performanceBar');
                if (performanceBar) {
                    performanceBar.style.width = data.stats.performance + '%';
                }

                this.updateElement('updatesCount', data.stats.updates_count);

                // Add pulse animation to updated elements
                this.pulseElement('statTotalCars');
                this.pulseElement('statActiveRentals');
            }
        } catch (error) {
            console.error('Error updating dashboard stats:', error);
        }
    }

    // Update Notifications
    async updateNotifications() {
        if (!this.isActive) return;

        try {
            const response = await fetch('api/notifications.php?action=get');
            const data = await response.json();

            if (data.success && data.notifications) {
                const notificationsList = document.getElementById('notificationsList');
                if (!notificationsList) return;

                let html = '';
                data.notifications.forEach(notification => {
                    const iconMap = {
                        'booking': 'calendar-check',
                        'maintenance': 'tools',
                        'payment': 'money-bill-wave',
                        'alert': 'exclamation-triangle'
                    };

                    const colorMap = {
                        'booking': 'success',
                        'maintenance': 'warning',
                        'payment': 'info',
                        'alert': 'danger'
                    };

                    const icon = iconMap[notification.type] || 'bell';
                    const color = colorMap[notification.type] || 'primary';

                    html += `
                    <div class="notification-item mb-2 p-2 border-bottom border-secondary">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-${icon} text-${color} mt-1 me-2"></i>
                            <div class="flex-grow-1">
                                <small class="d-block fw-bold">${notification.title}</small>
                                <small class="text-muted">${notification.message}</small>
                                <small class="d-block text-end text-muted mt-1">${timeAgo(notification.created_at)}</small>
                            </div>
                        </div>
                    </div>`;
                });

                notificationsList.innerHTML = html || '<p class="text-muted text-center">D' *H,/ %49'1'*</p>';
            }
        } catch (error) {
            console.error('Error updating notifications:', error);
        }
    }

    // Update Activity Log
    async updateActivityLog() {
        if (!this.isActive) return;

        try {
            const response = await fetch('api/activity.php?action=recent');
            const data = await response.json();

            if (data.success && data.activities) {
                const activityBody = document.getElementById('activityBody');
                if (!activityBody) return;

                let html = '';
                data.activities.forEach(activity => {
                    html += `
                    <tr>
                        <td>
                            <i class="fas fa-${activity.icon} me-2 text-${activity.color}"></i>
                            ${activity.action}
                        </td>
                        <td>${activity.user}</td>
                        <td>${timeAgo(activity.created_at)}</td>
                        <td><small class="text-muted">${activity.details}</small></td>
                    </tr>`;
                });

                activityBody.innerHTML = html || '<tr><td colspan="4" class="text-center text-muted">D' *H,/ F4'7'*</td></tr>';
            }
        } catch (error) {
            console.error('Error updating activity log:', error);
        }
    }

    // Update Recent Bookings
    async updateRecentBookings() {
        if (!this.isActive) return;

        try {
            const response = await fetch('api/bookings.php?action=recent');
            const data = await response.json();

            if (data.success && data.bookings) {
                const bookingsBody = document.getElementById('recentBookingsBody');
                if (!bookingsBody) return;

                let html = '';
                data.bookings.forEach(booking => {
                    const statusBadges = {
                        'active': '<span class="badge bg-success">F47</span>',
                        'pending': '<span class="badge bg-warning">BJ/ 'D'F*8'1</span>',
                        'completed': '<span class="badge bg-info">EC*ED</span>',
                        'cancelled': '<span class="badge bg-danger">ED:J</span>'
                    };

                    html += `
                    <tr>
                        <td>#${booking.id}</td>
                        <td>${booking.customer_name}</td>
                        <td>${booking.car_name}</td>
                        <td>${formatDate(booking.start_date)} %DI ${formatDate(booking.end_date)}</td>
                        <td>${formatCurrency(booking.total)}</td>
                        <td>${statusBadges[booking.status] || statusBadges.pending}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-light" onclick="viewBooking(${booking.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick="sendWhatsAppReminder(${booking.id})">
                                <i class="fab fa-whatsapp"></i>
                            </button>
                        </td>
                    </tr>`;
                });

                bookingsBody.innerHTML = html || '<tr><td colspan="7" class="text-center text-muted">D' *H,/ -,H2'*</td></tr>';
            }
        } catch (error) {
            console.error('Error updating recent bookings:', error);
        }
    }

    // Update Date and Time
    updateDateTime() {
        const currentTime = document.getElementById('currentTime');
        const currentDate = document.getElementById('currentDate');

        if (!currentTime && !currentDate) return;

        const now = new Date();

        if (currentTime) {
            const time = now.toLocaleTimeString('ar-EG', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
            currentTime.textContent = time;
        }

        if (currentDate) {
            const date = now.toLocaleDateString('ar-EG', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            currentDate.textContent = date;
        }
    }

    // Helper: Update Element Content
    updateElement(elementId, content) {
        const element = document.getElementById(elementId);
        if (element && element.textContent !== content.toString()) {
            element.textContent = content;
        }
    }

    // Helper: Add pulse animation to element
    pulseElement(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.add('realtime-update');
            setTimeout(() => element.classList.remove('realtime-update'), 2000);
        }
    }
}

/**
 * ============================================
 * Chart Updates
 * ============================================
 */

class ChartManager {
    constructor() {
        this.charts = {};
    }

    // Initialize charts
    init() {
        this.initRevenueChart();
        this.initBookingDistributionChart();
    }

    // Initialize Revenue Chart
    initRevenueChart() {
        const canvas = document.getElementById('revenueChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        this.charts.revenue = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['JF'J1', 'A(1'J1', 'E'13', '#(1JD', 'E'JH', 'JHFJH'],
                datasets: [{
                    label: ''D%J1'/'*',
                    data: [0, 0, 0, 0, 0, 0],
                    borderColor: '#FF5722',
                    backgroundColor: 'rgba(255, 87, 34, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: 'white' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: 'white',
                            callback: value => value + ' ª'
                        },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    },
                    x: {
                        ticks: { color: 'white' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    }
                }
            }
        });

        // Load data
        this.updateRevenueChart();
    }

    // Initialize Booking Distribution Chart
    initBookingDistributionChart() {
        const canvas = document.getElementById('bookingDistributionChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        this.charts.distribution = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['F47', 'EC*ED', 'BJ/ 'D'F*8'1', 'ED:J'],
                datasets: [{
                    data: [0, 0, 0, 0],
                    backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: 'white',
                            padding: 20
                        }
                    }
                }
            }
        });

        // Load data
        this.updateBookingDistributionChart();
    }

    // Update Revenue Chart
    async updateRevenueChart() {
        try {
            const response = await fetch('api/dashboard.php?action=get_revenue_data');
            const data = await response.json();

            if (data.success && this.charts.revenue) {
                this.charts.revenue.data.datasets[0].data = data.revenue;
                this.charts.revenue.update('none'); // Update without animation
            }
        } catch (error) {
            console.error('Error updating revenue chart:', error);
        }
    }

    // Update Booking Distribution Chart
    async updateBookingDistributionChart() {
        try {
            const response = await fetch('api/dashboard.php?action=get_booking_distribution');
            const data = await response.json();

            if (data.success && this.charts.distribution) {
                this.charts.distribution.data.datasets[0].data = data.distribution;
                this.charts.distribution.update('none'); // Update without animation
            }
        } catch (error) {
            console.error('Error updating booking distribution chart:', error);
        }
    }
}

/**
 * ============================================
 * Initialize Real-time Updates
 * ============================================
 */

let realtimeManager = null;
let chartManager = null;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize real-time manager
    realtimeManager = new RealtimeManager(30000); // 30 seconds
    realtimeManager.start();

    // Initialize charts if on dashboard
    if (document.getElementById('revenueChart') || document.getElementById('bookingDistributionChart')) {
        chartManager = new ChartManager();
        chartManager.init();

        // Update charts every 5 minutes
        setInterval(() => {
            if (chartManager.charts.revenue) chartManager.updateRevenueChart();
            if (chartManager.charts.distribution) chartManager.updateBookingDistributionChart();
        }, 300000);
    }

    // Stop updates when page is not visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            if (realtimeManager) realtimeManager.stop();
        } else {
            if (realtimeManager) realtimeManager.start();
        }
    });
});

// Stop updates before page unload
window.addEventListener('beforeunload', function() {
    if (realtimeManager) realtimeManager.stop();
});

console.log('Realtime.js loaded successfully');
