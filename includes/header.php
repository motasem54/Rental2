<?php
// includes/header.php
if (!isset($pageTitle)) {
    $pageTitle = 'نظام تأجير السيارات المتقدم';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="نظام تأجير السيارات المتقدم - إدارة شاملة لتأجير السيارات">
    <meta name="keywords" content="تأجير سيارات, إدارة سيارات, حجز سيارات">
    <meta name="author" content="نظام تأجير السيارات المتقدم">

    <title><?php echo $pageTitle; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts - Cairo for Arabic -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">

    <!-- Toastify CSS for Notifications -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <style>
        :root {
            /* Orange & White Theme */
            --primary: #FF8C42;
            --primary-dark: #FF6B35;
            --primary-light: #FFA566;
            --secondary: #FFFFFF;
            --background: #F8F9FA;
            --background-dark: #E9ECEF;
            --text-primary: #2C3E50;
            --text-secondary: #5A6C7D;
            --text-muted: #95A5A6;
            --glass: rgba(255, 255, 255, 0.95);
            --glass-border: rgba(255, 140, 66, 0.2);
            --blur-amount: 20px;
            --transition-speed: 0.3s;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(255, 140, 66, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #FFFFFF 0%, #F8F9FA 100%);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 99999;
        }

        .loading-overlay.active {
            display: flex;
        }

        .spinner-border-orange {
            color: var(--primary);
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/mobile-header.php'; ?>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center">
            <div class="spinner-border spinner-border-orange" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">جاري التحميل...</span>
            </div>
            <p class="mt-3 text-primary">جاري التحميل...</p>
        </div>
    </div>