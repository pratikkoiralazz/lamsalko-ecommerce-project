<?php
// includes/admin-sidebar.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/admin-auth.php';

// Determine active link for highlighting
$current_uri = $_SERVER['REQUEST_URI'];
function is_active($path, $uri) {
    return strpos($uri, $path) !== false ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            width: 260px;
            background-color: #212529;
        }
        .sidebar .nav-link {
            color: #adb5bd;
            font-size: 0.95rem;
            padding: 0.65rem 1rem;
            border-radius: 0.375rem;
            margin-bottom: 0.2rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #ffffff;
            background-color: #0d6efd;
        }
        .sidebar .nav-link i {
            margin-right: 0.6rem;
        }
    </style>
</head>
<body class="bg-light">
<div class="d-flex">
    <!-- Sidebar Navigation -->
    <div class="sidebar p-3 text-white flex-shrink-0">
        <a href="<?= SITE_URL ?>/admin/index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <i class="bi bi-speedometer2 fs-4 me-2 text-primary"></i>
            <span class="fs-5 fw-bold">Admin </span>
        </a>
        <hr class="border-secondary">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="<?= SITE_URL ?>/admin/index.php" class="nav-link <?= is_active('/admin/index.php', $current_uri) ?>">
                    <i class="bi bi-house-door"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="<?= SITE_URL ?>/admin/cars/index.php" class="nav-link <?= is_active('/admin/cars/', $current_uri) ?>">
                    <i class="bi bi-car-front"></i> Car Inventory
                </a>
            </li>
            <li>
                <a href="<?= SITE_URL ?>/admin/orders/index.php" class="nav-link <?= is_active('/admin/orders/', $current_uri) ?>">
                    <i class="bi bi-cart-check"></i> Orders & Sales
                </a>
            </li>
            <li>
                <a href="<?= SITE_URL ?>/admin/payments/index.php" class="nav-link <?= is_active('/admin/payments/', $current_uri) ?>">
                    <i class="bi bi-credit-card"></i> Payments
                </a>
            </li>
            <li>
                <a href="<?= SITE_URL ?>/admin/bookings/index.php" class="nav-link <?= is_active('/admin/bookings/', $current_uri) ?>">
                    <i class="bi bi-calendar-event"></i> Test Drives
                </a>
            </li>
            <li>
                <a href="<?= SITE_URL ?>/admin/customers/index.php" class="nav-link <?= is_active('/admin/customers/', $current_uri) ?>">
                    <i class="bi bi-people"></i> Customers
                </a>
            </li>
            <li>
                <a href="<?= SITE_URL ?>/admin/invoices/index.php" class="nav-link <?= is_active('/admin/invoices/', $current_uri) ?>">
                    <i class="bi bi-receipt"></i> Invoices
                </a>
            </li>
            <li>
                <a href="<?= SITE_URL ?>/admin/reports/index.php" class="nav-link <?= is_active('/admin/reports/', $current_uri) ?>">
                    <i class="bi bi-graph-up-arrow"></i> Analytics & Reports
                </a>
            </li>
            <li>
                <a href="<?= SITE_URL ?>/admin/inquiries/index.php" class="nav-link <?= is_active('/admin/inquiries/', $current_uri) ?>">
                    <i class="bi bi-envelope"></i> Contact Inquiries
                </a>
            </li>
            <?php if (($_SESSION['admin_role'] ?? '') === 'Super Admin'): ?>
            <li>
                <a href="<?= SITE_URL ?>/admin/admins/index.php" class="nav-link <?= is_active('/admin/admins/', $current_uri) ?>">
                    <i class="bi bi-shield-lock"></i> Staff Accounts
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="<?= SITE_URL ?>/admin/settings/index.php" class="nav-link <?= is_active('/admin/settings/', $current_uri) ?>">
                    <i class="bi bi-gear"></i> Showroom Settings
                </a>
            </li>
            <li>
                <a href="<?= SITE_URL ?>/admin/audit-logs/index.php" class="nav-link <?= is_active('/admin/audit-logs/', $current_uri) ?>">
                    <i class="bi bi-journal-text"></i> Audit Logs
                </a>
            </li>
        </ul>
        <hr class="border-secondary">
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle fs-5 me-2"></i>
                <strong><?= sanitize($_SESSION['admin_name'] ?? 'Admin') ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/admin/logout.php">Sign out</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <div class="flex-grow-1 p-4 bg-light overflow-auto">