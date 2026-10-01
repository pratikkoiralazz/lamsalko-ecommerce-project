<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

require_admin_login();

// Restrict access to Super Admin only
if ($_SESSION['admin_role'] !== 'Super Admin') {
    die("Access denied. Super Admin privileges required.");
}

$db = Database::getConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    if (!CSRF::checkToken($_POST['csrf_token'] ?? '')) {
        $error = "CSRF Verification Failed.";
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $role = sanitize($_POST['role'] ?? 'Admin');
        $password = $_POST['password'] ?? '';

        if ($name && $email && $password) {
            $pwd_hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO admins (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $pwd_hash, $role]);
            log_audit_action($_SESSION['admin_id'], 'ADMIN_CREATED', "Created new staff admin: {$email}");
            redirect('admin/admins/index.php');
        } else {
            $error = "Please provide all required credentials.";
        }
    }
}

$admins = $db->query("SELECT * FROM admins ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Administrators - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Administrator & Staff Management</h3>
        <a href="../index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= sanitize($error) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-3">Add New Admin User</h5>
                <form method="POST">
                    <?php CSRF::insertField(); ?>
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select name="role" class="form-select">
                            <option value="Admin">Admin</option>
                            <option value="Staff">Staff</option>
                            <option value="Super Admin">Super Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" name="add_admin" class="btn btn-primary w-100">Create Account</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm p-3">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $a): ?>
                            <tr>
                                <td>#<?= $a['id'] ?></td>
                                <td><strong><?= sanitize($a['name']) ?></strong></td>
                                <td><?= sanitize($a['email']) ?></td>
                                <td><span class="badge bg-primary"><?= $a['role'] ?></span></td>
                                <td><span class="badge bg-success"><?= $a['status'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>