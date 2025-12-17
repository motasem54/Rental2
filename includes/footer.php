    <!-- Footer (if needed for specific pages) -->
    <?php if (isset($showFooter) && $showFooter): ?>
    <footer class="footer mt-5 py-3 glass-card">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">
                        © <?php echo date('Y'); ?> F8'E *#,J1 'D3J'1'* 'DE*B/E - ,EJ9 'D-BHB E-AH8)
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <small>
                        'D%5/'1 2.0.0 |
                        <a href="#" class="text-decoration-none text-primary">'D/9E 'DAFJ</a> |
                        <a href="#" class="text-decoration-none text-primary">3J'3) 'D.5H5J)</a>
                    </small>
                </div>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <!-- JavaScript Libraries -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

    <!-- Flatpickr JS (Date Picker) -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom JavaScript -->
    <script src="<?php echo BASE_URL; ?>assets/js/app.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/ajax-handler.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/realtime.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/whatsapp.js"></script>

    <!-- Additional JS for specific pages -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Inline JavaScript -->
    <?php if (isset($inlineJS)): ?>
    <script>
        <?php echo $inlineJS; ?>
    </script>
    <?php endif; ?>

    <!-- Initialize Common Components -->
    <script>
    $(document).ready(function() {
        // Initialize DataTables
        if ($.fn.DataTable) {
            $('.datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
                },
                responsive: true,
                pageLength: 25
            });
        }

        // Initialize Select2
        if ($.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap-5',
                dir: 'rtl',
                language: 'ar'
            });
        }

        // Initialize Flatpickr
        if (typeof flatpickr !== 'undefined') {
            flatpickr('.datepicker', {
                locale: 'ar',
                dateFormat: 'Y-m-d',
                allowInput: true
            });

            flatpickr('.datetimepicker', {
                locale: 'ar',
                enableTime: true,
                dateFormat: 'Y-m-d H:i',
                time_24hr: false,
                allowInput: true
            });

            flatpickr('.timepicker', {
                locale: 'ar',
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                time_24hr: false,
                allowInput: true
            });
        }

        // Initialize Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize Popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    });

    // Hide loading overlay
    function hideLoading() {
        document.getElementById('loadingOverlay').classList.remove('active');
    }

    // Show loading overlay
    function showLoading() {
        document.getElementById('loadingOverlay').classList.add('active');
    }

    // Hide loading on page load
    window.addEventListener('load', function() {
        hideLoading();
    });
    </script>

</body>
</html>
