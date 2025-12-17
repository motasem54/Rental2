<!-- Mobile Header -->
<div class="mobile-header">
    <button class="mobile-menu-btn" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>
    <div class="mobile-logo">
        <i class="fas fa-car"></i>
        <span>تأجير السيارات</span>
    </div>
    <button class="mobile-logout-btn" onclick="window.location.href='../../logout.php'">
        <i class="fas fa-sign-out-alt"></i>
    </button>
</div>

<style>
    .mobile-header {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 60px;
        background: linear-gradient(135deg, #FF5722 0%, #FF7043 100%);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        z-index: 1000;
        align-items: center;
        justify-content: space-between;
        padding: 0 15px;
    }

    .mobile-menu-btn,
    .mobile-logout-btn {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 45px;
        height: 45px;
        border-radius: 10px;
        font-size: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .mobile-menu-btn:hover,
    .mobile-logout-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.05);
    }

    .mobile-logo {
        display: flex;
        align-items: center;
        gap: 8px;
        color: white;
        font-size: 16px;
        font-weight: bold;
    }

    .mobile-logo i {
        font-size: 24px;
    }

    @media (max-width: 768px) {
        .mobile-header {
            display: flex;
        }

        body {
            padding-top: 60px;
        }

        .main-content {
            margin-right: 0 !important;
            margin-left: 0 !important;
            padding-top: 20px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mobileMenuBtn = document.getElementById('mobileMenuToggle');
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function () {
                const sidebar = document.querySelector('.sidebar');
                const overlay = document.getElementById('sidebarOverlay') || createOverlay();

                if (sidebar) {
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                }
            });
        }

        function createOverlay() {
            let overlay = document.createElement('div');
            overlay.id = 'sidebarOverlay';
            overlay.className = 'sidebar-overlay';
            overlay.onclick = function () {
                document.querySelector('.sidebar').classList.remove('show');
                this.classList.remove('show');
            };
            document.body.appendChild(overlay);
            return overlay;
        }
    });
</script>