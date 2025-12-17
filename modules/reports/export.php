<?php
require_once '../../config/database.php';
require_once '../../core/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$pageTitle = 'تصدير التقارير';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../../assets/css/glassmorphism.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/rtl.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php $currentDir = 'reports'; include '../../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid">
            <h2><i class="fas fa-file-export me-2"></i><?php echo $pageTitle; ?></h2>
            
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="glass-card text-center p-4">
                        <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                        <h4>تصدير PDF</h4>
                        <p class="text-muted">تصدير التقارير بصيغة PDF</p>
                        <button class="btn btn-danger w-100" onclick="exportReport('pdf')">
                            <i class="fas fa-download me-2"></i>تصدير PDF
                        </button>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="glass-card text-center p-4">
                        <i class="fas fa-file-excel fa-3x text-success mb-3"></i>
                        <h4>تصدير Excel</h4>
                        <p class="text-muted">تصدير البيانات بصيغة Excel</p>
                        <button class="btn btn-success w-100" onclick="exportReport('excel')">
                            <i class="fas fa-download me-2"></i>تصدير Excel
                        </button>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="glass-card text-center p-4">
                        <i class="fas fa-file-csv fa-3x text-warning mb-3"></i>
                        <h4>تصدير CSV</h4>
                        <p class="text-muted">تصدير البيانات بصيغة CSV</p>
                        <button class="btn btn-warning w-100" onclick="exportReport('csv')">
                            <i class="fas fa-download me-2"></i>تصدير CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/app.js"></script>
    <script>
    async function exportReport(format) {
        try {
            const response = await fetch(\`../../api/reports.php?action=export&format=\${format}\`);
            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = \`report_\${new Date().toISOString().split('T')[0]}.\${format}\`;
                a.click();
            }
        } catch (error) {
            alert('حدث خطأ أثناء التصدير');
        }
    }
    </script>
</body>
</html>
