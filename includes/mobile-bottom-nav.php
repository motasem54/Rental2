<!-- Mobile Bottom Navigation -->
<nav class="mobile-bottom-nav">
    <a href="/dashboard.php" class="bottom-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
        <i class="fas fa-home"></i>
        <span>الرئيسية</span>
    </a>
    
    <a href="/modules/cars/index.php" class="bottom-nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'cars') !== false ? 'active' : ''; ?>">
        <i class="fas fa-car"></i>
        <span>السيارات</span>
    </a>
    
    <a href="/modules/rentals/create.php" class="bottom-nav-item">
        <i class="fas fa-plus-circle"></i>
        <span>حجز جديد</span>
    </a>
    
    <a href="/modules/bookings/index.php" class="bottom-nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'bookings') !== false ? 'active' : ''; ?>">
        <i class="fas fa-calendar-check"></i>
        <span>الحجوزات</span>
        <?php if (isset($pending_bookings) && $pending_bookings > 0): ?>
        <span class="badge"><?php echo $pending_bookings; ?></span>
        <?php endif; ?>
    </a>
    
    <a href="/modules/reports/financial.php" class="bottom-nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'reports') !== false ? 'active' : ''; ?>">
        <i class="fas fa-chart-line"></i>
        <span>التقارير</span>
    </a>
</nav>

<!-- Mobile Header -->
<div class="mobile-header hide-desktop">
    <div class="mobile-header-left">
        <button class="mobile-menu-button" onclick="toggleMobileSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="mobile-header-title"><?php echo $pageTitle ?? 'نظام تأجير السيارات'; ?></h1>
    </div>
    <div class="mobile-header-right">
        <button class="mobile-menu-button" onclick="showNotifications()">
            <i class="fas fa-bell"></i>
            <?php if (isset($total_notifications) && $total_notifications > 0): ?>
            <span class="badge" style="position: absolute; top: 5px; right: 5px;"><?php echo $total_notifications; ?></span>
            <?php endif; ?>
        </button>
    </div>
</div>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" onclick="toggleMobileSidebar()"></div>

<script>
function toggleMobileSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
}

function showNotifications() {
    // Implement notifications modal
    alert('سيتم إضافة نظام الإشعارات قريباً');
}

// Close sidebar when clicking on nav items (mobile)
if (window.innerWidth <= 768) {
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        link.addEventListener('click', () => {
            toggleMobileSidebar();
        });
    });
}

// Handle back button on mobile
window.addEventListener('popstate', function() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar.classList.contains('show')) {
        toggleMobileSidebar();
    }
});
</script>