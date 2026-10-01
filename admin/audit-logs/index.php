<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin_login();

$db = Database::getConnection();

$stmt = $db->query("
    SELECT a.*, adm.name as admin_name 
    FROM audit_logs a 
    LEFT JOIN admins adm ON a.admin_id = adm.id 
    ORDER BY a.id DESC 
    LIMIT 100
");
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Logs - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Security Audit Trail</h3>
        <a href="../index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    <div class="card border-0 shadow-sm p-3">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Timestamp</th>
                    <th>Admin User</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><small><?= $log['created_at'] ?></small></td>
                        <td><strong><?= sanitize($log['admin_name'] ?? 'System') ?></strong></td>
                        <td><span class="badge bg-dark"><?= sanitize($log['action']) ?></span></td>
                        <td><?= sanitize($log['description']) ?></td>
                        <td><code><?= sanitize($log['ip_address']) ?></code></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>