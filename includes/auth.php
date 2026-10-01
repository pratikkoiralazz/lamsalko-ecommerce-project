<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

function is_customer_logged_in(): bool {
    return isset($_SESSION['customer_id']) && !empty($_SESSION['customer_id']);
}

function require_customer_login(): void {
    if (!is_customer_logged_in()) {
        $_SESSION['flash_error'] = "Please log in to perform this action.";
        redirect('login.php');
    }
}

function is_admin_logged_in(): bool {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function require_admin_login(): void {
    if (!is_admin_logged_in()) {
        redirect('admin/login.php');
    }
}

function log_audit_action(?int $admin_id, string $action, string $description): void {
    $db = Database::getConnection();
    $stmt = $db->prepare("INSERT INTO audit_logs (admin_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt->execute([$admin_id, $action, $description, $ip]);
}