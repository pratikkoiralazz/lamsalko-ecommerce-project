<?php
// Core System Settings
define('SITE_NAME', 'HDC Car Showroom');
define('SITE_URL', 'http://localhost/car_showroom');
define('UPLOAD_DIR', __DIR__ . '/../uploads/cars/');
define('UPLOAD_URL', SITE_URL . '/uploads/cars/');

// Session Management
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Error Handling (Set to 0 in Production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Standard Helpers
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header("Location: " . SITE_URL . '/' . ltrim($path, '/'));
    exit();
}