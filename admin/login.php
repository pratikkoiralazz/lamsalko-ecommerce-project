<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::checkToken($_POST['csrf_token'] ?? '')) {
        $error = "Security validation failed.";
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM admins WHERE email = ? AND status = 'Active'");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_name'] = $admin['name'];

            log_audit_action($admin['id'], 'ADMIN_LOGIN', 'Admin authenticated successfully.');
            redirect('admin/index.php');
        } else {
            $error = "Invalid administrator credentials.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Apex Motors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center justify-content-center" style="height: 100vh;">
<div class="card shadow p-4" style="width: 100%; max-width: 400px;">
    <h4 class="text-center mb-4 fw-bold">Admin Portal</h4>
    <?php if ($error): ?>
        <div class="alert alert-danger py-2 small"><?= sanitize($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <?php CSRF::insertField(); ?>
        <div class="mb-3">
            <label class="form-label">Username / Email</label>
            <input type="text" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Sign In</button>
    </form>
</div>
</body>
</html>