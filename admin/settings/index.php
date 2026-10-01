<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

require_admin_login();

$db = Database::getConnection();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::checkToken($_POST['csrf_token'] ?? '')) {
        $msg = '<div class="alert alert-danger">CSRF Validation Failed.</div>';
    } else {
        foreach ($_POST['settings'] as $key => $value) {
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([sanitize($key), sanitize($value), sanitize($value)]);
        }
        log_audit_action($_SESSION['admin_id'], 'SETTINGS_UPDATE', "Updated global system settings.");
        $msg = '<div class="alert alert-success">System settings saved successfully!</div>';
    }
}

$raw_settings = $db->query("SELECT * FROM settings")->fetchAll();
$settings = [];
foreach ($raw_settings as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Settings - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Showroom Global Configuration</h3>
        <a href="../index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <?= $msg ?>

    <div class="card border-0 shadow-sm p-4 col-md-8 mx-auto">
        <form method="POST">
            <?php CSRF::insertField(); ?>
            <div class="mb-3">
                <label class="form-label fw-bold">Showroom Name</label>
                <input type="text" name="settings[showroom_name]" value="<?= sanitize($settings['showroom_name'] ?? '') ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Showroom Email</label>
                <input type="email" name="settings[showroom_email]" value="<?= sanitize($settings['showroom_email'] ?? '') ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Phone Number</label>
                <input type="text" name="settings[showroom_phone]" value="<?= sanitize($settings['showroom_phone'] ?? '') ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Address</label>
                <input type="text" name="settings[showroom_address]" value="<?= sanitize($settings['showroom_address'] ?? '') ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Currency Symbol</label>
                <input type="text" name="settings[currency_symbol]" value="<?= sanitize($settings['currency_symbol'] ?? 'NPR ') ?>" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Save System Settings</button>
        </form>
    </div>
</div>
</body>
</html>